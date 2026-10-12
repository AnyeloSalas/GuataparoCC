<?php
// src/Controllers/EstadoCivilController.php

require_once PROJECT_ROOT . '/src/Models/EstadoCivil.php';
require_once PROJECT_ROOT . '/src/Models/Csrf.php';

class EstadoCivilController {
    private $db;
    private $estadoCivil;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->estadoCivil = new EstadoCivil($this->db);
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
        
        $total_registros = $this->estadoCivil->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->estadoCivil->read($offset, $por_pagina);
        $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        require_once PROJECT_ROOT . '/src/Views/estado_civil/index.php';
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
                $this->estadoCivil->nombre = $_POST['nombre'];
                $this->estadoCivil->genero = $_POST['genero'] ?? 'N';
                $this->estadoCivil->descripcion = $_POST['descripcion'] ?? '';
                $this->estadoCivil->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->estadoCivil->nombre)) {
                    $error = "El estado civil solo puede contener letras y espacios.";
                } elseif ($this->estadoCivil->existsByName($this->estadoCivil->nombre)) {
                    $error = "El estado civil '" . htmlspecialchars($this->estadoCivil->nombre) . "' ya se encuentra registrado.";
                } elseif ($this->estadoCivil->create()) {
                    header("Location: index.php?action=estado_civil&success=created");
                    exit();
                } else {
                    $error = "Error al crear el estado civil.";
                }
            }
        }

        require PROJECT_ROOT . '/src/Views/estado_civil/create.php';
    }

    public function edit() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=estado_civil");
            exit();
        }

        $this->estadoCivil->id = $_GET['id'];
        if (!$this->estadoCivil->readOne()) {
            header("Location: index.php?action=estado_civil&error=not_found");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad.";
            } else {
                $this->estadoCivil->nombre = $_POST['nombre'];
                $this->estadoCivil->genero = $_POST['genero'] ?? 'N';
                $this->estadoCivil->descripcion = $_POST['descripcion'] ?? '';
                $this->estadoCivil->activo = $_POST['activo'] ?? 1;

                if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $this->estadoCivil->nombre)) {
                    $error = "El estado civil solo puede contener letras y espacios.";
                } elseif ($this->estadoCivil->update()) {
                    header("Location: index.php?action=estado_civil&success=updated");
                    exit();
                } else {
                    $error = "Error al actualizar el estado civil.";
                }
            }
        }

        $estado = $this->estadoCivil;
        require PROJECT_ROOT . '/src/Views/estado_civil/edit.php';
    }

    public function delete() {
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=estado_civil&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=estado_civil&error=invalid_token");
            exit();
        }

        if (isset($_POST['id'])) {
            $this->estadoCivil->id = $_POST['id'];
            try {
                if ($this->estadoCivil->delete()) {
                    header("Location: index.php?action=estado_civil&success=deleted");
                    exit();
                }
            } catch (Exception $e) {
                header("Location: index.php?action=estado_civil&error=delete_failed_fk");
                exit();
            }
        }

        header("Location: index.php?action=estado_civil&error=delete_failed");
        exit();
    }
}
?>
