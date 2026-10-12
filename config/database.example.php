<?php
/**
 * Archivo de configuración de ejemplo
 * Copie este archivo como database.php y configure sus credenciales
 */

class Database {
    // Configuración de la base de datos
    private $host = "localhost";
    private $db_name = "guataparo";
    private $username = "root";
    private $password = "";
    private $charset = "utf8";
    
    public $conn;

    /**
     * Obtener conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Error de conexión a la base de datos. Por favor contacte al administrador.");
        }

        return $this->conn;
    }
}
?>
