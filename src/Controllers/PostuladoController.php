<?php
require_once __DIR__ . '/../Models/Validator.php';
require_once __DIR__ . '/../Models/Postulado.php';
require_once PROJECT_ROOT . '/src/Models/Profesion.php';

class PostuladoController {
    private $db;
    private $postulado;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->postulado = new Postulado($this->db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $filtros = [
            'nombre' => get('search_nombre'),
            'cedula' => get('search_cedula'),
            'keyword' => get('search')
        ];
        
        $search = $filtros;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        $totalRecords = $this->postulado->count($search);
        $totalPages = ceil($totalRecords / $limit);
        
        if ($page < 1) $page = 1;
        if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $limit;

        $stmt = $this->postulado->read($search, $limit, $offset);
        $postulados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once PROJECT_ROOT . '/src/Views/postulados/index.php';
    }

    public function create() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=postulados");
            exit();
        }

        $error = null;
        $success = null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Verificar CSRF token
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad. Por favor, intente nuevamente.";
            } else {
                $this->postulado->tipomiembro = $_POST['tipomiembro'] ?? 'PRINCIPAL';
                $this->postulado->primer_nombre = $_POST['primer_nombre'];
                $this->postulado->segundo_nombre = $_POST['segundo_nombre'] ?? '';
                $this->postulado->primer_apellido = $_POST['primer_apellido'];
                $this->postulado->segundo_apellido = $_POST['segundo_apellido'] ?? '';
                $this->postulado->correo_electronico = $_POST['correo_electronico'] ?? '';
                $this->postulado->profesion = $_POST['profesion'] ?? '';
                $this->postulado->tipo_referencia = $_POST['tipo_referencia'] ?? '';
                $this->postulado->referencia_id = $_POST['referencia_id'] ?? '';
                $nacionalidad_id = $_POST['nacionalidad_id'] ?? 1;
                $this->postulado->nacionalidad_id = $nacionalidad_id;

                // Guardar solo la parte numérica en cedula (el prefijo va en nacionalidad_id)
                $this->postulado->cedula = preg_replace('/[^0-9]/', '', $_POST['cedula']);
                
                // Validar Email
                if (!empty($this->postulado->correo_electronico) && !Validator::validateEmail($this->postulado->correo_electronico)) {
                    $error = "El formato del correo electrónico no es válido.";
                } else {
                    // Verificar si ya existe un postulado con esta cédula
                if ($this->postulado->readOne()) {
                    $error = "Ya existe un postulado registrado con la cédula " . $this->postulado->cedula . ".";
                } else {
                    // Handle Photo Upload with strict validation
                    $photoData = $this->validateAndGetUploadedPhoto($error);
                    if ($error) {
                        // Error handled by re-rendering view below
                    } else {
                        $this->postulado->foto = $photoData;

                        if ($this->postulado->create()) {
                            header("Location: index.php?action=postulados&success=created");
                            exit();
                        } else {
                            $error = "Error al crear postulado. Verifique los datos e intente nuevamente.";
                        }
                    }
                }
            }
        }
    }

        
        $profesionModel = new Profesion($this->db);
        $socios = $this->postulado->getSociosReferencia()->fetchAll(PDO::FETCH_ASSOC);
        $empresas = $this->postulado->getEmpresasReferencia()->fetchAll(PDO::FETCH_ASSOC);
        $profesiones = $profesionModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
        $nacionalidades = $this->postulado->getNacionalidades()->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Views/postulados/create.php';
    }

    public function edit() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=postulados");
            exit();
        }

        $error = null;
        $success = null;

        if (!isset($_GET['cedula'])) {
            header("Location: index.php?action=postulados");
            exit();
        }

        $this->postulado->cedula = $_GET['cedula'];
        
        if (!$this->postulado->readOne()) {
            header("Location: index.php?action=postulados&error=not_found");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Verificar CSRF token
            if (!Csrf::validateRequest()) {
                $error = "Error de validación de seguridad. Por favor, intente nuevamente.";
            } else {
                $this->postulado->tipomiembro = $_POST['tipomiembro'];
                $this->postulado->primer_nombre = $_POST['primer_nombre'];
                $this->postulado->segundo_nombre = $_POST['segundo_nombre'] ?? '';
                $this->postulado->primer_apellido = $_POST['primer_apellido'];
                $this->postulado->segundo_apellido = $_POST['segundo_apellido'] ?? '';
                // $this->postulado->accion = $_POST['accion'] ?? ''; removed
                $this->postulado->correo_electronico = $_POST['correo_electronico'] ?? '';
                $this->postulado->profesion = $_POST['profesion'] ?? '';
                $this->postulado->tipo_referencia = $_POST['tipo_referencia'] ?? '';
                $this->postulado->referencia_id = $_POST['referencia_id'] ?? '';
                $this->postulado->nacionalidad_id = $_POST['nacionalidad_id'] ?? 1;

                // Validar Email
                if (!empty($this->postulado->correo_electronico) && !Validator::validateEmail($this->postulado->correo_electronico)) {
                    $error = "El formato del correo electrónico no es válido.";
                } else {
                    // Handle Photo Upload with strict validation
                    $photoData = $this->validateAndGetUploadedPhoto($error);
                    if ($error) {
                        // Error handled by re-rendering view below
                    } else {
                        if ($photoData !== null) {
                            $this->postulado->foto = $photoData;
                        }
                        // If no new file uploaded, keep existing photo (already in $this->postulado->foto from readOne)

                        if ($this->postulado->update()) {
                            header("Location: index.php?action=postulados&success=updated");
                            exit();
                        } else {
                            $error = "Error al actualizar postulado.";
                        }
                    }
                }
            }
        }

        $profesionModel = new Profesion($this->db);
        $postulado = $this->postulado;
        $socios = $this->postulado->getSociosReferencia()->fetchAll(PDO::FETCH_ASSOC);
        $empresas = $this->postulado->getEmpresasReferencia()->fetchAll(PDO::FETCH_ASSOC);
        $profesiones = $profesionModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
        $nacionalidades = $this->postulado->getNacionalidades()->fetchAll(PDO::FETCH_ASSOC);
        require_once PROJECT_ROOT . '/src/Views/postulados/edit.php';
    }

    public function delete() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=postulados");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=postulados&error=method_not_allowed");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            header("Location: index.php?action=postulados&error=invalid_token");
            exit();
        }

        if (isset($_POST['cedula'])) {
            $this->postulado->cedula = $_POST['cedula'];
            if ($this->postulado->delete()) {
                header("Location: index.php?action=postulados&success=deleted");
                exit();
            }
        }
        
        header("Location: index.php?action=postulados&error=delete_failed");
        exit();
    }

    /**
     * Valida de forma estricta el archivo de imagen subido (MIME, tamaño y dimensiones).
     * 
     * @param string|null &$error Mensaje de error retornado por referencia en caso de falla.
     * @return string|null|false Contenido binario de la imagen, null si no se subió archivo, o false si es inválida.
     */
    private function validateAndGetUploadedPhoto(&$error) {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $error = "Error al subir la imagen (Código: " . $_FILES['foto']['error'] . ").";
            return false;
        }

        // 1. Limitar tamaño máximo a 2MB
        $maxSize = 2 * 1024 * 1024;
        if ($_FILES['foto']['size'] > $maxSize) {
            $error = "La foto no debe superar los 2MB de tamaño.";
            return false;
        }

        // 2. Validar tipo MIME real inspeccionando los bytes del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes)) {
            $error = "Formato de imagen inválido. Solo se permiten archivos JPG, PNG y WEBP.";
            return false;
        }

        // 3. Validar que la cabecera sea una imagen decodificable
        $imgInfo = @getimagesize($_FILES['foto']['tmp_name']);
        if ($imgInfo === false) {
            $error = "El archivo seleccionado no es una imagen válida o está dañado.";
            return false;
        }

        return file_get_contents($_FILES['foto']['tmp_name']);
    }
}
?>
