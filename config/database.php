<?php
/**
 * Clase de Conexión a la Base de Datos (Database Connection Class)
 * 
 * Gestiona la conexión a la base de datos utilizando PDO.
 * Las credenciales y configuraciones se cargan desde el archivo .env
 * para mantener la seguridad y flexibilidad del entorno.
 */
class Database {
    /** @var string Host de la base de datos */
    private $host;
    
    /** @var string Nombre de la base de datos */
    private $db_name;
    
    /** @var string Usuario de la base de datos */
    private $username;
    
    /** @var string Contraseña de la base de datos */
    private $password;
    
    /** @var string Conjunto de caracteres (ej. utf8mb4) */
    private $charset;
    
    /** @var PDO|null Instancia de la conexión PDO */
    public $conn;

    /**
     * Constructor de la clase Database.
     * Inicializa las propiedades de conexión leyendo las variables de entorno.
     */
    public function __construct() {
        // Asegurar que BASE_PATH esté definido si este archivo se carga fuera del flujo principal (bootstrap)
        if (!defined('BASE_PATH')) {
            // El archivo está en /config/database.php, por lo que el directorio raíz es el padre de /config
            define('BASE_PATH', dirname(__DIR__));
        }
        
        // Cargar variables de entorno desde el archivo .env
        $this->loadEnv();

        // Asignar valores desde las variables de entorno.
        // No se usan valores por defecto para forzar el uso del archivo .env por seguridad.
        $this->host = $this->env('DB_HOST');
        $this->db_name = $this->env('DB_NAME');
        $this->username = $this->env('DB_USER');
        $this->password = $this->env('DB_PASS');
        $this->charset = $this->env('DB_CHARSET', 'utf8mb4');
    }

    /**
     * Carga manualmente el archivo .env si no fue cargado previamente en la petición actual.
     * Lee el archivo línea por línea y establece las variables de entorno.
     * 
     * @return void
     */
    private function loadEnv() {
        if (file_exists(BASE_PATH . '/.env')) {
            $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignorar líneas que son comentarios
                if (strpos(trim($line), '#') === 0) continue;
                
                // Procesar líneas que contienen una asignación (clave=valor)
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key, " \t\n\r\0\x0B\xEF\xBB\xBF");
                    $value = trim(trim($value), '"\'');
                    if ($key !== '') {
                        putenv("$key=$value");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Función auxiliar para obtener una variable de entorno con soporte para un valor por defecto.
     * 
     * @param string $key Nombre de la variable de entorno a buscar.
     * @param mixed $default Valor por defecto si la variable no existe.
     * @return mixed El valor de la variable de entorno o el valor por defecto.
     */
    private function env($key, $default = null) {
        // Buscar en getenv, _ENV y _SERVER para máxima compatibilidad entre diferentes configuraciones de servidor
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if ($value === false && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }

        if ($value === false) {
            // Si una variable crítica no existe y no hay valor por defecto, se detiene la ejecución con un error claro
            if ($default === null && in_array($key, ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])) {
                $base = BASE_PATH;
                $envExists = file_exists($base . '/.env') ? 'EXISTE' : 'NO EXISTE EN ' . $base;
                die("Error Crítico: La variable de entorno $key no está definida. [.env: $envExists]");
            }
            return $default;
        }
        return $value;
    }

    /**
     * Establece y devuelve la conexión a la base de datos utilizando PDO.
     * Configura el manejo de errores, el modo de obtención de datos y desactiva la emulación de sentencias preparadas.
     * 
     * @return PDO|null Retorna la instancia de PDO si es exitoso, o termina la ejecución en caso de error crítico.
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Construir el Data Source Name (DSN) para la conexión PDO
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurar atributos de PDO para un comportamiento seguro y predecible
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Lanzar excepciones en errores
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Devolver arrays asociativos por defecto
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Usar sentencias preparadas nativas para mayor seguridad

            // Si hay una sesión activa, establecer el usuario actual en la base de datos
            // Esto es útil para triggers de auditoría que registran quién realizó un cambio
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['usuario'])) {
                $user = $this->conn->quote($_SESSION['usuario']);
                $this->conn->exec("SET @current_user_id = $user");
            }
        } catch(PDOException $exception) {
            $logMessage = date('[Y-m-d H:i:s] ') . "Error de conexión a la base de datos: " . $exception->getMessage() . "\n";
            
            // En entorno de producción, registrar el error en un archivo log y mostrar un mensaje genérico
            if (getenv('APP_ENV') === 'production') {
                $logFile = dirname(__DIR__) . '/logs/database_' . date('Y-m-d') . '.log';
                error_log($logMessage, 3, $logFile);
                die("Error de conexión a la base de datos. Por favor contacte al administrador.");
            } else {
                // En entorno de desarrollo, mostrar el error detallado para facilitar la depuración
                die("Error de Conexión a la Base de Datos: " . $exception->getMessage());
            }
        }

        return $this->conn;
    }
}
?>
