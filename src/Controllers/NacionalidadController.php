<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../Models/Nacionalidad.php';

class NacionalidadController extends AdminController {
    private $nacionalidad;

    public function __construct() {
        parent::__construct();
        $this->verifyRole('Administrador');
        $this->nacionalidad = new Nacionalidad($this->db);
    }

    public function index() {
        // Paginación
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $por_pagina = 10;
        if ($pagina < 1) $pagina = 1;
        
        $total_registros = $this->nacionalidad->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->nacionalidad->read($offset, $por_pagina);
        $nacionalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once PROJECT_ROOT . '/src/Views/nacionalidades/index.php';
    }

    public function create() {
        if (isPost()) {
            if (!Csrf::validateRequest()) {
                flash('error', 'Error de seguridad CSRF.');
                redirect('index.php?action=nacionalidades');
            }

            $this->nacionalidad->codigo = post('codigo');
            $this->nacionalidad->nombre = post('nombre');
            $this->nacionalidad->activo = isset($_POST['activo']) ? 1 : 0;

            if (!preg_match('/^[a-zA-Z]$/', $this->nacionalidad->codigo)) {
                $error = "El código debe contener un solo dígito y debe ser una letra.";
                require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
            } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->nacionalidad->nombre)) {
                $error = "El nombre solo puede contener letras y espacios.";
                require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
            } elseif ($this->nacionalidad->existsByCodigo($this->nacionalidad->codigo)) {
                $error = "El código de nacionalidad '" . htmlspecialchars($this->nacionalidad->codigo) . "' ya se encuentra registrado.";
                require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
            } elseif ($this->nacionalidad->existsByName($this->nacionalidad->nombre)) {
                $error = "La nacionalidad '" . htmlspecialchars($this->nacionalidad->nombre) . "' ya se encuentra registrada.";
                require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
            } elseif ($this->nacionalidad->create()) {
                flash('success', 'Nacionalidad creada exitosamente.');
                redirect('index.php?action=nacionalidades');
            } else {
                $error = 'Error al crear la nacionalidad.';
                require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
            }
        } else {
            require PROJECT_ROOT . '/src/Views/nacionalidades/create.php';
        }
    }

    public function edit() {
        $id = get('id');
        if (!$id) {
            redirect('index.php?action=nacionalidades');
        }

        $this->nacionalidad->id = $id;
        if (!$this->nacionalidad->readOne()) {
            flash('error', 'Nacionalidad no encontrada.');
            redirect('index.php?action=nacionalidades');
        }

        if (isPost()) {
            if (!Csrf::validateRequest()) {
                flash('error', 'Error de seguridad CSRF.');
                redirect('index.php?action=nacionalidades');
            }

            $this->nacionalidad->codigo = post('codigo');
            $this->nacionalidad->nombre = post('nombre');
            $this->nacionalidad->activo = isset($_POST['activo']) ? 1 : 0;

            if (!preg_match('/^[a-zA-Z]$/', $this->nacionalidad->codigo)) {
                $error = "El código debe contener un solo dígito y debe ser una letra.";
                $nacionalidad = $this->nacionalidad;
                require_once PROJECT_ROOT . '/src/Views/nacionalidades/edit.php';
            } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->nacionalidad->nombre)) {
                $error = "El nombre solo puede contener letras y espacios.";
                $nacionalidad = $this->nacionalidad;
                require_once PROJECT_ROOT . '/src/Views/nacionalidades/edit.php';
            } elseif ($this->nacionalidad->update()) {
                flash('success', 'Nacionalidad actualizada exitosamente.');
                redirect('index.php?action=nacionalidades');
            } else {
                $error = 'Error al actualizar la nacionalidad.';
                $nacionalidad = $this->nacionalidad;
                require_once PROJECT_ROOT . '/src/Views/nacionalidades/edit.php';
            }
        } else {
            $nacionalidad = $this->nacionalidad;
            require_once PROJECT_ROOT . '/src/Views/nacionalidades/edit.php';
        }
    }

    public function delete() {
        if (!isPost()) {
            flash('error', 'Método no permitido.');
            redirect('index.php?action=nacionalidades');
            return;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido.');
            redirect('index.php?action=nacionalidades');
            return;
        }

        $id = post('id');
        if ($id) {
            $this->nacionalidad->id = $id;
            if ($this->nacionalidad->delete()) {
                flash('success', 'Nacionalidad eliminada exitosamente.');
            } else {
                flash('error', 'Error al eliminar la nacionalidad.');
            }
        }
        redirect('index.php?action=nacionalidades');
    }
}
