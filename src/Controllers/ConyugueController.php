<?php
require_once __DIR__ . '/../Models/Socio.php';
require_once __DIR__ . '/../Models/Nacionalidad.php';

class ConyugueController {
    private $db;
    private $conyugue;
    private $socio;
    private $nacionalidad;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->conyugue = new Conyugue($this->db);
        $this->socio = new Socio($this->db);
        $this->nacionalidad = new Nacionalidad($this->db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $stmt = $this->conyugue->read($search, $limit, $offset);
        $conyugues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalRecords = $this->conyugue->count($search);
        $totalPages = ceil($totalRecords / $limit);

        require PROJECT_ROOT . '/src/Views/conyugues/index.php';
    }

    public function create() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                header("Location: index.php?action=conyugues");
                exit();
            }

            $this->conyugue->accionconyugue = $_POST['accionconyugue'];
            $this->conyugue->cedulaconyugue = preg_replace('/[^0-9]/', '', $_POST['cedulaconyugue']);
            $this->conyugue->nacionalidad_id = $this->getNacionalidadId($_POST['nacionalidad'] ?? 'V');
            $this->conyugue->primer_nombre = $_POST['primer_nombre'];
            $this->conyugue->segundo_nombre = $_POST['segundo_nombre'];
            $this->conyugue->primer_apellido = $_POST['primer_apellido'];
            $this->conyugue->segundo_apellido = $_POST['segundo_apellido'];
            $this->conyugue->tipo_relacion = $_POST['tipo_relacion'];
            $this->conyugue->estado_civil = $_POST['estado_civil'];

            // Validar si la cédula ya existe para otro familiar
            if ($this->conyugue->existsByCedula($this->conyugue->cedulaconyugue)) {
                $error = "La cédula '" . htmlspecialchars($_POST['cedulaconyugue']) . "' ya se encuentra registrada para otro familiar.";
                
                // Repoblar datos para el formulario
                $stmt_socios = $this->socio->read();
                $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
                $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
                $stmt_rel->execute();
                $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
                $nacionalidades = $this->nacionalidad->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/conyugues/create.php';
                return;
            }

            if ($this->conyugue->create()) {
                flash('success', 'Familiar registrado exitosamente.');
                header("Location: index.php?action=conyugues");
                exit();
            } else {
                $error = "Error al crear familiar. Verifique que no exista un registro previo para esta acción.";
                // Get socios for dropdown
                $stmt_socios = $this->socio->read();
                $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
                // Get relations
                $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
                $stmt_rel->execute();
                $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
                $nacionalidades = $this->nacionalidad->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/conyugues/create.php';
            }
        } else {
            // Get socios for dropdown
            $stmt_socios = $this->socio->read();
            $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
            // Get relations
            $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
            $stmt_rel->execute();
            $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tipos_relacion)) {
                $tipos_relacion = ['Esposa/o', 'Hijo/a', 'Padre', 'Madre', 'Concubino/a'];
            }
            $nacionalidades = $this->nacionalidad->readActive()->fetchAll(PDO::FETCH_ASSOC);
            require_once PROJECT_ROOT . '/src/Views/conyugues/create.php';
        }
    }

    public function edit() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        if (isset($_GET['accion'])) {
            $this->conyugue->accionconyugue = $_GET['accion'];
            if ($this->conyugue->readOne()) {
                // Get socios for dropdown
                $stmt_socios = $this->socio->read();
                $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
                // Get relations
                $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
                $stmt_rel->execute();
                $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
                if (empty($tipos_relacion)) {
                    $tipos_relacion = ['Esposa/o', 'Hijo/a', 'Padre', 'Madre', 'Concubino/a'];
                }
                $nacionalidades = $this->nacionalidad->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require_once PROJECT_ROOT . '/src/Views/conyugues/edit.php';
            } else {
                header("Location: index.php?action=conyugues");
                exit();
            }
        } else {
            header("Location: index.php?action=conyugues");
            exit();
        }
    }

    public function update() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                header("Location: index.php?action=conyugues");
                exit();
            }

            $this->conyugue->accionconyugue = $_POST['accionconyugue'];
            $this->conyugue->cedulaconyugue = preg_replace('/[^0-9]/', '', $_POST['cedulaconyugue']);
            $this->conyugue->nacionalidad_id = $this->getNacionalidadId($_POST['nacionalidad'] ?? 'V');
            $this->conyugue->primer_nombre = $_POST['primer_nombre'];
            $this->conyugue->segundo_nombre = $_POST['segundo_nombre'];
            $this->conyugue->primer_apellido = $_POST['primer_apellido'];
            $this->conyugue->segundo_apellido = $_POST['segundo_apellido'];
            $this->conyugue->tipo_relacion = $_POST['tipo_relacion'];
            $this->conyugue->estado_civil = $_POST['estado_civil'];

            // Validar si la cédula ya existe para otro familiar (excluyendo la acción actual)
            if ($this->conyugue->existsByCedula($this->conyugue->cedulaconyugue, $this->conyugue->accionconyugue)) {
                $error = "La cédula '" . htmlspecialchars($_POST['cedulaconyugue']) . "' ya se encuentra registrada para otro familiar.";
                
                // Recargar datos necesarios para la vista de edición
                $this->conyugue->readOne();
                $stmt_socios = $this->socio->read();
                $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
                $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
                $stmt_rel->execute();
                $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
                $nacionalidades = $this->nacionalidad->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require_once PROJECT_ROOT . '/src/Views/conyugues/edit.php';
                return;
            }

            if ($this->conyugue->update()) {
                flash('success', 'Familiar actualizado exitosamente.');
                header("Location: index.php?action=conyugues");
                exit();
            } else {
                $error = "Error al actualizar familiar.";
                $this->conyugue->readOne();
                // Get socios for dropdown
                $stmt_socios = $this->socio->read();
                $socios = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
                // Get relations
                $stmt_rel = $this->db->prepare("SELECT nombre FROM tipos_relacion ORDER BY nombre ASC");
                $stmt_rel->execute();
                $tipos_relacion = $stmt_rel->fetchAll(PDO::FETCH_COLUMN);
                require_once PROJECT_ROOT . '/src/Views/conyugues/edit.php';
            }
        } else {
            header("Location: index.php?action=conyugues");
            exit();
        }
    }

    public function delete() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=conyugues&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=conyugues&error=invalid_token");
            exit();
        }

        if (isset($_POST['accion'])) {
            $this->conyugue->accionconyugue = $_POST['accion'];
            if ($this->conyugue->delete()) {
                header("Location: index.php?action=conyugues");
                exit();
            } else {
                header("Location: index.php?action=conyugues&error=delete_failed");
                exit();
            }
        } else {
            header("Location: index.php?action=conyugues");
            exit();
        }
    }

    /**
     * Obtener el ID de nacionalidad a partir del código (V, E, P)
     */
    private function getNacionalidadId($codigo) {
        $stmt = $this->db->prepare("SELECT id FROM nacionalidades WHERE codigo = ? LIMIT 1");
        $stmt->execute([strtoupper(trim($codigo))]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            // Default: buscar 'V'
            $stmt = $this->db->prepare("SELECT id FROM nacionalidades WHERE codigo = 'V' LIMIT 1");
            $stmt->execute();
            $id = $stmt->fetchColumn();
        }
        return $id;
    }
}
?>
