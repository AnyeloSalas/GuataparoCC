<?php
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../Models/Configuracion.php';

class ConfiguracionController extends AdminController {
    private $configuracion;

    public function __construct() {
        parent::__construct();
        $this->verifyRole('Administrador'); // Strictly admin only
        $this->configuracion = new Configuracion($this->db);
    }

    public function index() {
        // Get all configurations grouped by category
        $stmt = $this->configuracion->getAll();
        $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by category
        $configPorCategoria = [];
        foreach ($configuraciones as $config) {
            $categoria = $config['categoria'] ?? 'general';
            if (!isset($configPorCategoria[$categoria])) {
                $configPorCategoria[$categoria] = [];
            }
            $configPorCategoria[$categoria][] = $config;
        }

        require_once __DIR__ . '/../Views/configuracion/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Token de seguridad inválido. Por favor intente nuevamente.';
                header("Location: index.php?action=configuracion");
                exit();
            }

            require_once __DIR__ . '/../Models/Validator.php';
            $errores = [];
            $actualizados = 0;

            foreach ($_POST as $key => $value) {
                // Skip action parameter and csrf_token
                if ($key == 'action' || $key == 'csrf_token') continue;
                
                $cleanKey = Validator::clean($key);
                $cleanValue = Validator::clean($value);
                
                if ($this->configuracion->update($cleanKey, $cleanValue)) {
                    $actualizados++;
                } else {
                    $errores[] = "Error al actualizar: " . htmlspecialchars($cleanKey);
                }
            }
            
            // Set success or error message
            if (count($errores) > 0) {
                $_SESSION['error'] = implode('<br>', $errores);
            } else {
                $_SESSION['success'] = "Configuración actualizada exitosamente. ($actualizados configuraciones actualizadas)";
            }
            
            // Redirect back with message
            header("Location: index.php?action=configuracion");
            exit();
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Token de seguridad inválido. Por favor intente nuevamente.';
                header("Location: index.php?action=configuracion");
                exit();
            }

            $clave = $_POST['clave'] ?? '';
            $valor = $_POST['valor'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $tipo = $_POST['tipo'] ?? 'text';
            $categoria = $_POST['categoria'] ?? 'general';

            if (empty($clave) || empty($descripcion)) {
                $_SESSION['error'] = 'La clave y descripción son obligatorias.';
                header("Location: index.php?action=configuracion");
                exit();
            }

            if ($this->configuracion->create($clave, $valor, $descripcion, $tipo, $categoria)) {
                $_SESSION['success'] = 'Configuración creada exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al crear la configuración. La clave puede estar duplicada.';
            }

            header("Location: index.php?action=configuracion");
            exit();
        }

        require_once __DIR__ . '/../Views/configuracion/create.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header("Location: index.php?action=configuracion");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor intente nuevamente.';
            header("Location: index.php?action=configuracion");
            exit();
        }

        $clave = $_POST['clave'] ?? '';

        if (empty($clave)) {
            $_SESSION['error'] = 'Clave de configuración no especificada.';
            header("Location: index.php?action=configuracion");
            exit();
        }

        if ($this->configuracion->delete($clave)) {
            $_SESSION['success'] = 'Configuración eliminada exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar la configuración.';
        }

        header("Location: index.php?action=configuracion");
        exit();
    }

    public function reset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header("Location: index.php?action=configuracion");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Token CSRF inválido.';
            header("Location: index.php?action=configuracion");
            exit();
        }

        // This would reset configurations to default values
        // For now, just redirect with a message
        $_SESSION['info'] = 'Función de reseteo en desarrollo. Por favor, ejecute el script SQL manualmente.';
        header("Location: index.php?action=configuracion");
        exit();
    }
}
?>
