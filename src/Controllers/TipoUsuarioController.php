<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Models/TipoUsuario.php';

class TipoUsuarioController {
    private $db;
    private $tipoUsuario;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->tipoUsuario = new TipoUsuario($this->db);
    }

    public function index() {
        $stmt = $this->tipoUsuario->read();
        require PROJECT_ROOT . '/src/Views/tipos_usuario/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=tipos_usuario');
                return;
            }

            $this->tipoUsuario->nombre = post('nombre');
            $this->tipoUsuario->activo = post('activo') ? 1 : 0;

            if (empty($this->tipoUsuario->nombre)) {
                flash('error', 'El nombre es obligatorio.');
            } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->tipoUsuario->nombre)) {
                flash('error', 'El nombre solo puede contener letras y espacios.');
            } elseif ($this->tipoUsuario->existsByName($this->tipoUsuario->nombre)) {
                flash('error', "El tipo de usuario '" . htmlspecialchars($this->tipoUsuario->nombre) . "' ya existe.");
            } else {
                if ($this->tipoUsuario->create()) {
                    flash('success', 'Tipo de Usuario creado exitosamente.');
                } else {
                    flash('error', 'Error al crear el Tipo de Usuario.');
                }
            }
            redirect('index.php?action=tipos_usuario');
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=tipos_usuario');
                return;
            }

            $this->tipoUsuario->id = post('id');
            $this->tipoUsuario->nombre = post('nombre');
            $this->tipoUsuario->activo = post('activo') ? 1 : 0;

            if (empty($this->tipoUsuario->nombre) || empty($this->tipoUsuario->id)) {
                flash('error', 'El nombre y el ID son obligatorios.');
            } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->tipoUsuario->nombre)) {
                flash('error', 'El nombre solo puede contener letras y espacios.');
            } elseif ($this->tipoUsuario->existsByName($this->tipoUsuario->nombre, $this->tipoUsuario->id)) {
                flash('error', "El tipo de usuario '" . htmlspecialchars($this->tipoUsuario->nombre) . "' ya existe.");
            } else {
                if ($this->tipoUsuario->update()) {
                    flash('success', 'Tipo de Usuario actualizado exitosamente.');
                } else {
                    flash('error', 'Error al actualizar el Tipo de Usuario.');
                }
            }
            redirect('index.php?action=tipos_usuario');
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=tipos_usuario');
                return;
            }

            $this->tipoUsuario->id = post('id');
            
            // Protección: No permitir eliminar "Administrador" ni "SOCIO"
            if ($this->tipoUsuario->readOne()) {
                if (strtolower($this->tipoUsuario->nombre) === 'administrador' || strtolower($this->tipoUsuario->nombre) === 'socio') {
                    flash('error', 'No se puede eliminar el rol crítico del sistema: ' . $this->tipoUsuario->nombre);
                    redirect('index.php?action=tipos_usuario');
                    return;
                }
            }

            if (!empty($this->tipoUsuario->id)) {
                if ($this->tipoUsuario->delete()) {
                    flash('success', 'Tipo de Usuario eliminado exitosamente.');
                } else {
                    flash('error', 'Error al eliminar el Tipo de Usuario. Verifique que no esté en uso.');
                }
            }
            redirect('index.php?action=tipos_usuario');
        }
    }
}
?>
