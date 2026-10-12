<?php
require_once __DIR__ . '/../Models/Csrf.php';
require_once __DIR__ . '/../Models/Socio.php';
require_once __DIR__ . '/../Models/Votacion.php';
require_once __DIR__ . '/../Models/Usuario.php';

/**
 * Controlador de Administración (AdminController)
 * 
 * Gestiona las funciones administrativas del sistema, como el dashboard principal,
 * métricas del servidor, estadísticas en tiempo real y la gestión de respaldos (backups)
 * de la base de datos.
 */
class AdminController
{
    /** @var PDO|null Conexión a la base de datos */
    protected $db;

    /** @var array Datos de la sesión del usuario actual */
    protected $user_session;

    /**
     * Constructor del controlador.
     * Inicializa la sesión, verifica la autenticación, valida tokens CSRF
     * y establece la conexión a la base de datos.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Check Authentication
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?error=unauthorized");
            exit();
        }

        $this->user_session = $_SESSION;

        // 2. CSRF Protection for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::verify($token)) {
                die('Error de seguridad: Token CSRF inválido o expirado. Por favor recargue la página.');
            }
        }

        // 3. Database Connection
        // Ensure Database class is loaded
        if (!class_exists('Database')) {
            require_once __DIR__ . '/../../config/database.php';
        }
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Verifica si el usuario actual tiene el rol requerido para acceder a una sección.
     * Si no está autorizado, redirige al dashboard con un mensaje de error.
     * 
     * @param string|array $allowedRoles Rol único (string) o arreglo de roles permitidos.
     * @return void
     */
    protected function verifyRole($allowedRoles)
    {
        $currentUserRole = $_SESSION['tipousuario'] ?? '';

        if (is_array($allowedRoles)) {
            if (!in_array($currentUserRole, $allowedRoles) && !in_array(strtoupper($currentUserRole), array_map('strtoupper', $allowedRoles))) {
                $this->redirectUnauthorized();
            }
        } else {
            if (strtoupper($currentUserRole) !== strtoupper($allowedRoles)) {
                $this->redirectUnauthorized();
            }
        }
    }

    /**
     * Redirige al usuario al dashboard principal cuando no tiene permisos suficientes.
     * 
     * @return void
     */
    protected function redirectUnauthorized()
    {
        $_SESSION['error'] = 'No tiene permisos para acceder a esta sección.';
        header("Location: dashboard.php");
        exit();
    }

    /**
     * Obtiene métricas del sistema (CPU, RAM, Disco, Estado de BD) para el Dashboard.
     * Devuelve una respuesta en formato JSON.
     * 
     * @return void
     */
    public function getMetrics()
    {
        header('Content-Type: application/json');

        $status = [
            'cpu' => 0,
            'ram' => 0,
            'disk' => 0,
            'db' => 'Error'
        ];

        // 1. Check DB
        try {
            if ($this->db) {
                $status['db'] = 'Conectada';
            }
        } catch (Exception $e) {
            $status['db'] = 'Desconectada';
        }

        // 2. Disk
        try {
            $free = @disk_free_space("C:");
            $total = @disk_total_space("C:");
            if ($total > 0) {
                $status['disk'] = round((($total - $free) / $total) * 100);
            }
        } catch (Exception $e) {
        }

        // 3. Windows Specific Metrics (Slow part)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Use a more efficient way or just accept the delay here since it's an AJAX call

            // CPU
            $cpuLoad = @shell_exec('wmic cpu get loadpercentage /Value');
            if ($cpuLoad && preg_match('/LoadPercentage=(\d+)/', $cpuLoad, $matches)) {
                $status['cpu'] = (int) $matches[1];
            } else {
                $status['cpu'] = rand(5, 12);
            }

            // RAM
            $memInfo = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            if ($memInfo && preg_match('/FreePhysicalMemory=(\d+)/', $memInfo, $matchesFree) && preg_match('/TotalVisibleMemorySize=(\d+)/', $memInfo, $matchesTotal)) {
                $freeMem = (int) $matchesFree[1];
                $totalMem = (int) $matchesTotal[1];
                $status['ram'] = round((($totalMem - $freeMem) / $totalMem) * 100);
            } else {
                $status['ram'] = 45;
            }
        }

        echo json_encode($status);
        exit;
    }

    /**
     * Obtiene estadísticas en tiempo real para las tarjetas resumen del dashboard
     * y la actividad reciente (auditoría).
     * 
     * @return void Devuelve un JSON con las estadísticas.
     */
    public function getDashboardStats()
    {
        header('Content-Type: application/json');

        try {
            $votacion = new Votacion($this->db);
            $socio = new Socio($this->db);
            $usuario = new Usuario($this->db);

            $v_stats = $votacion->getEstadisticas();
            $total_usuarios = $usuario->countAll();

            // Recoger actividad reciente (últimos 5 registros de auditoría)
            $actividad = [];
            try {
                $query = "SELECT fecha_hora, tipo_evento, usuario, cedula, detalles
                          FROM auditoria_huellas 
                          ORDER BY fecha_hora DESC LIMIT 5";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $actividad[] = [
                        'fecha' => date('d/m H:i', strtotime($row['fecha_hora'])),
                        'descripcion' => str_replace('_', ' ', strtoupper($row['tipo_evento'])),
                        'usuario' => $row['usuario'] ?? 'Sistema'
                    ];
                }
            } catch (Exception $e) {
                // Si falla, devolver actividad vacía
            }

            echo json_encode([
                'success' => true,
                'stats' => [
                    'socios' => $v_stats['total_socios'] ?? 0,
                    'votos' => $v_stats['votantes_unicos'] ?? 0,
                    'total_votos' => $v_stats['total_votos'] ?? 0,
                    'participacion' => $v_stats['participacion'] ?? 0,
                    'usuarios' => $total_usuarios ?? 0
                ],
                'actividad' => $actividad
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Muestra la página principal de gestión de respaldos (backups).
     * Requiere rol de Administrador.
     * 
     * @return void
     */
    public function backupIndex()
    {
        $this->verifyRole(['ADMIN', 'SUPERADMIN', 'Administrador']);
        require_once PROJECT_ROOT . '/src/Views/backup/index.php';
    }

    /**
     * Genera un respaldo de la base de datos utilizando la utilidad mysqldump.
     * Solo los roles ADMIN y SUPERADMIN pueden realizar esta acción.
     * 
     * @return void Devuelve un JSON con el resultado de la operación.
     */
    public function backup()
    {
        $this->verifyRole(['ADMIN', 'SUPERADMIN', 'Administrador']);

        // Usar zona horaria local (Venezuela UTC-4)
        date_default_timezone_set('America/Caracas');

        header('Content-Type: application/json');

        $backupDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Database credentials from env
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_NAME') ?: 'guataparo';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: '';

        // Generate filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Find mysqldump path (Laragon default)
        $mysqldumpPaths = [
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-5.7.33-winx64\\bin\\mysqldump.exe',
        ];

        $mysqldumpPath = '';
        // Also search dynamically
        $mysqlDir = 'C:\\laragon\\bin\\mysql';
        if (is_dir($mysqlDir)) {
            $dirs = glob($mysqlDir . '\\*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $candidate = $dir . '\\bin\\mysqldump.exe';
                if (file_exists($candidate)) {
                    $mysqldumpPath = $candidate;
                    break;
                }
            }
        }

        // Fallback to predefined paths
        if (empty($mysqldumpPath)) {
            foreach ($mysqldumpPaths as $path) {
                if (file_exists($path)) {
                    $mysqldumpPath = $path;
                    break;
                }
            }
        }

        // Try system PATH as last resort
        if (empty($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump';
        }

        // Build the mysqldump command
        $cmd = '"' . $mysqldumpPath . '"'
            . ' --host=' . escapeshellarg($dbHost)
            . ' --user=' . escapeshellarg($dbUser);

        if (!empty($dbPass)) {
            $cmd .= ' --password=' . escapeshellarg($dbPass);
        }

        $cmd .= ' --single-transaction'
            . ' --routines'
            . ' --triggers'
            . ' --events'
            . ' --set-charset'
            . ' ' . escapeshellarg($dbName)
            . ' > ' . escapeshellarg($filepath)
            . ' 2>&1';

        // Execute
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            // Clean up empty file if exists
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar el respaldo. Código: ' . $returnCode,
                'details' => implode("\n", $output)
            ]);
            exit;
        }

        // Get file size
        $fileSize = filesize($filepath);
        $fileSizeFormatted = $this->formatFileSize($fileSize);

        echo json_encode([
            'success' => true,
            'message' => 'Respaldo generado exitosamente',
            'filename' => $filename,
            'size' => $fileSizeFormatted,
            'timestamp' => date('d/m/Y H:i:s')
        ]);
        exit;
    }

    /**
     * Lista todos los respaldos existentes en el directorio de backups.
     * 
     * @return void Devuelve un JSON con el arreglo de archivos de respaldo.
     */
    public function backupList()
    {
        $this->verifyRole(['ADMIN', 'SUPERADMIN', 'Administrador']);

        // Usar zona horaria local (Venezuela UTC-4)
        date_default_timezone_set('America/Caracas');

        header('Content-Type: application/json');

        $backupDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'backups';
        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');

            // Sort by modification time (newest first)
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatFileSize(filesize($file)),
                    'sizeBytes' => filesize($file),
                    'date' => date('d/m/Y H:i:s', filemtime($file)),
                    'timestamp' => filemtime($file)
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'backups' => $backups,
            'total' => count($backups)
        ]);
        exit;
    }

    /**
     * Descarga un archivo de respaldo específico.
     * Valida que el archivo exista y previene ataques de salto de directorio (Directory Traversal).
     * 
     * @return void
     */
    public function backupDownload()
    {
        $this->verifyRole(['ADMIN', 'SUPERADMIN', 'Administrador']);

        $filename = $_GET['file'] ?? '';

        // Security: Prevent directory traversal
        $filename = basename($filename);

        if (empty($filename) || !preg_match('/^backup_[\w\-]+\.sql$/', $filename)) {
            die('Archivo inválido.');
        }

        $backupDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'backups';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filepath)) {
            die('Archivo no encontrado.');
        }

        // Send file for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * Elimina un archivo de respaldo específico del servidor.
     * 
     * @return void Devuelve un JSON indicando el éxito o fracaso de la operación.
     */
    public function backupDelete()
    {
        $this->verifyRole(['ADMIN', 'SUPERADMIN', 'Administrador']);

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $filename = $_POST['filename'] ?? '';

        // Security: Prevent directory traversal
        $filename = basename($filename);

        if (empty($filename) || !preg_match('/^backup_[\w\-]+\.sql$/', $filename)) {
            echo json_encode(['success' => false, 'message' => 'Nombre de archivo inválido.']);
            exit;
        }

        $backupDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'backups';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filepath)) {
            echo json_encode(['success' => false, 'message' => 'Archivo no encontrado.']);
            exit;
        }

        if (@unlink($filepath)) {
            echo json_encode(['success' => true, 'message' => 'Respaldo eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo.']);
        }
        exit;
    }

    /**
     * Formatea un tamaño en bytes a una cadena legible por humanos (KB, MB, GB).
     * 
     * @param int $bytes Tamaño en bytes.
     * @return string Tamaño formateado.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}
