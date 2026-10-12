<?php
require_once __DIR__ . '/../Models/PuestoTrabajo.php';

class PuestoTrabajoController {
    private $db;
    private $puesto;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->puesto = new PuestoTrabajo($this->db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] !== 'Administrador') {
            header("Location: index.php");
            exit();
        }

        // Paginación
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $por_pagina = 10;
        if ($pagina < 1) $pagina = 1;
        
        $total_registros = $this->puesto->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->puesto->read($offset, $por_pagina);
        $puestos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require PROJECT_ROOT . '/src/Views/puestos_trabajo/index.php';
    }

    public function create() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] !== 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Token de seguridad inválido. Por favor intente nuevamente.';
                header("Location: index.php?action=puestos_trabajo");
                exit();
            }

            $this->puesto->nombre = $_POST['nombre'];
            $this->puesto->activo = isset($_POST['activo']) ? 1 : 0;

            if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->puesto->nombre)) {
                $_SESSION['flash_error'] = "El nombre solo puede contener letras y espacios.";
            } elseif ($this->puesto->existsByName($this->puesto->nombre)) {
                $_SESSION['flash_error'] = "El puesto de trabajo '" . htmlspecialchars($this->puesto->nombre) . "' ya se encuentra registrado.";
            } elseif ($this->puesto->create()) {
                $_SESSION['flash_success'] = "Puesto de trabajo creado exitosamente.";
            } else {
                $_SESSION['flash_error'] = "Error al crear puesto de trabajo.";
            }
            header("Location: index.php?action=puestos_trabajo");
            exit();
        }
    }

    public function update() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] !== 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Token de seguridad inválido. Por favor intente nuevamente.';
                header("Location: index.php?action=puestos_trabajo");
                exit();
            }

            $this->puesto->id = $_POST['id'];
            $this->puesto->nombre = $_POST['nombre'];
            $this->puesto->activo = isset($_POST['activo']) ? 1 : 0;

            if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->puesto->nombre)) {
                $_SESSION['flash_error'] = "El nombre solo puede contener letras y espacios.";
            } elseif ($this->puesto->update()) {
                $_SESSION['flash_success'] = "Puesto de trabajo actualizado exitosamente.";
            } else {
                $_SESSION['flash_error'] = "Error al actualizar puesto de trabajo.";
            }
            header("Location: index.php?action=puestos_trabajo");
            exit();
        }
    }

    public function delete() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] !== 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=puestos_trabajo&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=puestos_trabajo&error=invalid_token");
            exit();
        }

        if (isset($_POST['id'])) {
            $this->puesto->id = $_POST['id'];
            
            // Aquí se podría agregar validación si el puesto está en uso en usuarios
            if ($this->puesto->delete()) {
                $_SESSION['flash_success'] = "Puesto de trabajo eliminado exitosamente.";
                header("Location: index.php?action=puestos_trabajo");
            } else {
                header("Location: index.php?action=puestos_trabajo&error=delete_failed");
            }
            exit();
        } else {
            header("Location: index.php?action=puestos_trabajo");
            exit();
        }
    }
}
?>
