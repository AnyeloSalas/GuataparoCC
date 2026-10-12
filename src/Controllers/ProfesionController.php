<?php
// src/Controllers/ProfesionController.php

require_once PROJECT_ROOT . '/src/Models/Profesion.php';
require_once PROJECT_ROOT . '/src/Models/Csrf.php';

class ProfesionController {
    private $db;
    private $profesion;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->profesion = new Profesion($this->db);
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
        
        $total_registros = $this->profesion->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->profesion->read($offset, $por_pagina);
        $profesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        require_once PROJECT_ROOT . '/src/Views/profesiones/index.php';
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
                $this->profesion->nombre = trim($_POST['nombre']);
                $this->profesion->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $this->profesion->nombre)) {
                    $error = "El nombre de la profesión solo debe contener letras.";
                } elseif ($this->profesion->existsByName($this->profesion->nombre)) {
                    $error = "La profesión '" . htmlspecialchars($this->profesion->nombre) . "' ya se encuentra registrada.";
                } elseif ($this->profesion->create()) {
                    header("Location: index.php?action=profesiones&success=created");
                    exit();
                } else {
                    $error = "Error al crear la profesión.";
                }
            }
        }

        require PROJECT_ROOT . '/src/Views/profesiones/create.php';
    }

    public function edit() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=profesiones");
            exit();
        }

        $this->profesion->id = $_GET['id'];
        if (!$this->profesion->readOne()) {
            header("Location: index.php?action=profesiones&error=not_found");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad.";
            } else {
                $this->profesion->nombre = trim($_POST['nombre']);
                $this->profesion->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $this->profesion->nombre)) {
                    $error = "El nombre de la profesión solo debe contener letras.";
                } elseif ($this->profesion->update()) {
                    header("Location: index.php?action=profesiones&success=updated");
                    exit();
                } else {
                    $error = "Error al actualizar la profesión.";
                }
            }
        }

        $profesion = $this->profesion;
        require PROJECT_ROOT . '/src/Views/profesiones/edit.php';
    }

    public function delete() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=profesiones&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=profesiones&error=invalid_token");
            exit();
        }

        if (isset($_POST['id'])) {
            $this->profesion->id = $_POST['id'];
            if ($this->profesion->delete()) {
                header("Location: index.php?action=profesiones&success=deleted");
                exit();
            }
        }

        header("Location: index.php?action=profesiones&error=delete_failed");
        exit();
    }
}
?>
