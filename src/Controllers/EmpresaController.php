<?php
// src/Controllers/EmpresaController.php

class EmpresaController {
    private $db;
    private $empresa;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->empresa = new Empresa($this->db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $success = isset($_GET['success']) ? $_GET['success'] : null;
        $error = isset($_GET['error']) ? $_GET['error'] : null;

        // Paginación
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $por_pagina = 10;
        if ($pagina < 1) $pagina = 1;
        
        $total_registros = $this->empresa->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;
        
        $stmt = $this->empresa->read($offset, $por_pagina);
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once PROJECT_ROOT . '/src/Views/empresas/index.php';
    }

    public function create() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=empresas");
            exit();
        }

        $error = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de seguridad.";
            } else {
                $this->empresa->rif_empresa = trim($_POST['rif_empresa']);
                $this->empresa->nombre_empresa = trim($_POST['nombre_empresa']);
                $this->empresa->direccion_empresa = $_POST['direccion_empresa'] ?? '';
                $this->empresa->tipo_rif = $_POST['tipo_rif'] ?? 'J';

                if (!preg_match('/^[0-9]{9}$/', $this->empresa->rif_empresa)) {
                    $error = "El RIF de la empresa debe contener exactamente 9 números.";
                } elseif ($this->empresa->existsByRif($this->empresa->rif_empresa)) {
                    $error = "El RIF '" . htmlspecialchars($this->empresa->rif_empresa) . "' ya se encuentra registrado.";
                } elseif ($this->empresa->existsByName($this->empresa->nombre_empresa)) {
                    $error = "La empresa '" . htmlspecialchars($this->empresa->nombre_empresa) . "' ya se encuentra registrada.";
                } elseif ($this->empresa->create()) {
                    header("Location: index.php?action=empresas&success=created");
                    exit();
                } else {
                    $error = "Error al crear empresa.";
                }
            }
        }

        require PROJECT_ROOT . '/src/Views/empresas/create.php';
    }

    public function edit() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=empresas");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=empresas");
            exit();
        }

        $this->empresa->id_empresa = $_GET['id'];
        
        if (!$this->empresa->readOne()) {
            header("Location: index.php?action=empresas&error=not_found");
            exit();
        }

        $error = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Csrf::validateRequest()) {
                $error = "Error de seguridad.";
            } else {
                $this->empresa->rif_empresa = trim($_POST['rif_empresa']);
                $this->empresa->nombre_empresa = trim($_POST['nombre_empresa']);
                $this->empresa->direccion_empresa = $_POST['direccion_empresa'] ?? '';
                $this->empresa->tipo_rif = $_POST['tipo_rif'] ?? 'J';

                if (!preg_match('/^[0-9]{9}$/', $this->empresa->rif_empresa)) {
                    $error = "El RIF de la empresa debe contener exactamente 9 números.";
                } elseif ($this->empresa->existsByRif($this->empresa->rif_empresa, $this->empresa->id_empresa)) {
                    $error = "El RIF '" . htmlspecialchars($this->empresa->rif_empresa) . "' ya se encuentra registrado por otra empresa.";
                } elseif ($this->empresa->update()) {
                    header("Location: index.php?action=empresas&success=updated");
                    exit();
                } else {
                    $error = "Error al actualizar empresa.";
                }
            }
        }

        $empresa = $this->empresa;
        require PROJECT_ROOT . '/src/Views/empresas/edit.php';
    }

    public function delete() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=empresas");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=empresas&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=empresas&error=invalid_token");
            exit();
        }

        if (isset($_POST['id'])) {
            $this->empresa->id_empresa = $_POST['id'];
            if ($this->empresa->delete()) {
                header("Location: index.php?action=empresas&success=deleted");
                exit();
            }
        }
        
        header("Location: index.php?action=empresas&error=delete_failed");
        exit();
    }
}
?>
