<?php
// src/Controllers/TipoRelacionController.php

require_once PROJECT_ROOT . '/src/Models/TipoRelacion.php';
require_once PROJECT_ROOT . '/src/Models/Csrf.php';

class TipoRelacionController {
    private $db;
    private $tipoRelacion;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->tipoRelacion = new TipoRelacion($this->db);
    }

    public function index() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        // Paginación
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $por_pagina = 10;
        if ($pagina < 1) $pagina = 1;
        
        $total_registros = $this->tipoRelacion->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->tipoRelacion->read($offset, $por_pagina);
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        require_once PROJECT_ROOT . '/src/Views/tipos_relacion/index.php';
    }

    public function create() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad.";
            } else {
                $this->tipoRelacion->nombre = $_POST['nombre'];
                $this->tipoRelacion->descripcion = $_POST['descripcion'] ?? '';
                $this->tipoRelacion->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s\/]+$/', $this->tipoRelacion->nombre)) {
                    $error = "El nombre solo puede contener letras, espacios y el carácter /.";
                } elseif ($this->tipoRelacion->existsByName($this->tipoRelacion->nombre)) {
                    $error = "El tipo de relación '" . htmlspecialchars($this->tipoRelacion->nombre) . "' ya se encuentra registrado.";
                } elseif ($this->tipoRelacion->create()) {
                    header("Location: index.php?action=tipos_relacion&success=created");
                    exit();
                } else {
                    $error = "Error al crear el tipo de relación.";
                }
            }
        }

        require PROJECT_ROOT . '/src/Views/tipos_relacion/create.php';
    }

    public function edit() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=tipos_relacion");
            exit();
        }

        $this->tipoRelacion->id = $_GET['id'];
        if (!$this->tipoRelacion->readOne()) {
            header("Location: index.php?action=tipos_relacion&error=not_found");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad.";
            } else {
                $this->tipoRelacion->nombre = $_POST['nombre'];
                $this->tipoRelacion->descripcion = $_POST['descripcion'] ?? '';
                $this->tipoRelacion->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s\/]+$/', $this->tipoRelacion->nombre)) {
                    $error = "El nombre solo puede contener letras, espacios y el carácter /.";
                } elseif ($this->tipoRelacion->update()) {
                    header("Location: index.php?action=tipos_relacion&success=updated");
                    exit();
                } else {
                    $error = "Error al actualizar el tipo de relación.";
                }
            }
        }

        $tipoRelacion = $this->tipoRelacion;
        require PROJECT_ROOT . '/src/Views/tipos_relacion/edit.php';
    }

    public function delete() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=tipos_relacion&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=tipos_relacion&error=invalid_token");
            exit();
        }

        if (isset($_POST['id'])) {
            $this->tipoRelacion->id = $_POST['id'];
            try {
                if ($this->tipoRelacion->delete()) {
                    header("Location: index.php?action=tipos_relacion&success=deleted");
                    exit();
                }
            } catch (Exception $e) {
                header("Location: index.php?action=tipos_relacion&error=delete_failed_fk");
                exit();
            }
        }

        header("Location: index.php?action=tipos_relacion&error=delete_failed");
        exit();
    }
}
?>
