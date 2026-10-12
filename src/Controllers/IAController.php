<?php

class IAController {
    private $db;
    private $socio;
    private $votacion;
    private $conocimiento;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->socio = new Socio($this->db);
        $this->votacion = new Votacion($this->db);
        $this->conocimiento = new IAConocimiento($this->db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $stats = [
            'total_socios' => $this->socio->countAll(),
            'total_votaciones' => 0, 
            'fecha_analisis' => date('d/m/Y H:i')
        ];

        require_once PROJECT_ROOT . '/src/Views/ia/index.php';
    }

    public function entrenamiento() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=ia_dashboard");
            exit();
        }

        // Procesar formulario de guardado o eliminación vía POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                header("Location: index.php?action=ia_entrenamiento&error=invalid_token");
                exit();
            }

            if (isset($_POST['delete_id'])) {
                $this->conocimiento->id = $_POST['delete_id'];
                if ($this->conocimiento->delete()) {
                    header("Location: index.php?action=ia_entrenamiento&success=deleted");
                    exit();
                } else {
                    $error = "Error al eliminar el conocimiento.";
                }
            } elseif (isset($_POST['palabras_clave'])) {
                $this->conocimiento->palabras_clave = $_POST['palabras_clave'];
                $this->conocimiento->respuesta = $_POST['respuesta'];
                $this->conocimiento->categoria = $_POST['categoria'];

                if ($this->conocimiento->create()) {
                    $success = "Conocimiento agregado exitosamente.";
                } else {
                    $error = "Error al guardar el conocimiento.";
                }
            }
        }

        $stmt = $this->conocimiento->read();
        $conocimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Views/ia/entrenamiento.php';
    }

    public function procesarConsulta() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $pregunta = $input['pregunta'] ?? '';
        $respuesta = "";

        // 1. Buscar en Base de Conocimiento (Prioridad Alta)
        $respuestaBD = $this->conocimiento->buscarRespuesta($pregunta);
        
        if ($respuestaBD) {
            $respuesta = $respuestaBD;
        } 
        // 2. Respuestas Dinámicas del Sistema
        elseif (preg_match('/(buscar|quien es|consultar|info).*socio\s+(.+)/i', $pregunta, $matches)) {
            // Protección contra filtración de datos a usuarios anónimos
            if (!isset($_SESSION['usuario'])) {
                $respuesta = "Por motivos de privacidad y seguridad, debe iniciar sesión en el sistema para consultar información sobre socios.";
            } else {
                $termino = trim($matches[2]);
                // Usamos la búsqueda del modelo Socio
                $stmt = $this->socio->search($termino);
                $count = 0;
                $resultados = "";
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $count++;
                    if ($count > 3) break; // Limitar a 3 resultados
                    $resultados .= "- **{$row['nombre']}** (Acción: {$row['accion']}, Cédula: {$row['cedula']}, Estado: {$row['estado']})\n";
                }
                
                if ($count > 0) {
                    $respuesta = "He encontrado la siguiente información sobre '{$termino}':\n" . $resultados;
                    if ($count > 3) $respuesta .= "... y otros resultados más.";
                } else {
                    $respuesta = "No encontré ningún socio que coincida con '{$termino}'.";
                }
            }
        }
        elseif (stripos($pregunta, 'socios') !== false && stripos($pregunta, 'cuantos') !== false) {
            if (!isset($_SESSION['usuario'])) {
                $respuesta = "Para consultar estadísticas del club, debe iniciar sesión en el sistema.";
            } else {
                $total = $this->socio->countAll();
                $respuesta = "Actualmente contamos con un total de **{$total} socios** registrados en el sistema.";
            }
        } 
        elseif (stripos($pregunta, 'hora') !== false) {
            $respuesta = "Son las " . date('h:i A') . ".";
        }
        elseif (preg_match('/(conyuge|esposo|esposa)\s+de\s+(.+)/i', $pregunta, $matches)) {
            if (!isset($_SESSION['usuario'])) {
                $respuesta = "Para ver la información familiar, por favor inicie sesión en el sistema.";
            } else {
                $respuesta = "Para ver la información familiar, por favor utiliza el comando 'buscar socio [nombre]' y revisa su perfil en el sistema.";
            }
        }
        // 3. Respuesta por defecto
        else {
            $sugerencias = "- Preguntar por fechas de elecciones o estatutos\n- Horarios o ayuda de acceso";
            if (isset($_SESSION['usuario'])) {
                $sugerencias .= "\n- 'Buscar socio [nombre]'\n- 'Cuantos socios hay'";
            }
            $respuesta = "No tengo información específica sobre eso. Puedes probar:\n" . $sugerencias;
        }

        echo json_encode(['respuesta' => $respuesta]);
        exit;
    }
}
?>
