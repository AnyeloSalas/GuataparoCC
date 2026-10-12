<?php
require_once __DIR__ . '/../bootstrap.php';

class PermisoController {
    private $db;
    private $permisoModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->permisoModel = new Permiso($this->db);
    }

    public function index() {
        // Verificar que el usuario sea administrador
        if (strtolower($_SESSION['tipousuario']) !== 'administrador') {
            flash('error', 'No tiene permisos para acceder a este módulo.');
            header("Location: dashboard.php");
            exit();
        }

        // Obtener la lista de roles
        $stmt_roles = $this->permisoModel->getTiposUsuario();
        
        // Obtener los módulos definidos
        $stmt_modulos = $this->permisoModel->getAllModulos();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                header("Location: index.php?action=permisos");
                exit();
            }

            $tipousuario = $_POST['tipousuario'] ?? '';
            $modulos_seleccionados = $_POST['modulos'] ?? [];

            if (!empty($tipousuario) && strtolower($tipousuario) !== 'administrador' && strtolower($tipousuario) !== 'socio') {
                if ($this->permisoModel->updatePermisos($tipousuario, $modulos_seleccionados)) {
                    flash('success', 'Permisos actualizados con éxito.');
                } else {
                    flash('error', 'Error al actualizar permisos.');
                }
            } else {
                flash('warning', 'No se puede modificar este tipo de usuario.');
            }
            header("Location: index.php?action=permisos");
            exit();
        }

        // Por defecto, mostrar permisos del primer rol en la lista (si lo hay)
        // Pero primero mapear todo para AJAX u opciones
        // Una manera fácil es tener una solicitud AJAX o pasar los datos al frontend.
        // Haremos un arreglo asociativo para pasar a la vista
        $roles_permisos = [];
        $roles = [];
        
        while ($row = $stmt_roles->fetch(PDO::FETCH_ASSOC)) {
            $rol = $row['tipousuario'];
            $roles[] = $rol;
            $roles_permisos[$rol] = $this->permisoModel->getPermisosByRole($rol);
        }

        require_once PROJECT_ROOT . '/src/Views/permisos/index.php';
    }
}
?>
