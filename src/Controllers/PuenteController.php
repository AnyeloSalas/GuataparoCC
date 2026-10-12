<?php
require_once __DIR__ . '/AdminController.php';

class PuenteController extends AdminController {

    public function __construct() {
        parent::__construct();
        // Restringir a administradores o usuarios con permiso
        $this->verifyRole('Administrador');
    }

    public function index() {
        $status = $this->checkStatus();
        $logs = $this->getLogs();
        
        require_once __DIR__ . '/../Views/puente/index.php';
    }

    public function start() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido. Use POST.';
            header("Location: index.php?action=puente");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token CSRF inválido o expirado.';
            header("Location: index.php?action=puente");
            exit();
        }

        if (!$this->checkStatus()) {
            $bridgeUrl = getenv('BRIDGE_LOCAL_URL') ?: 'http://localhost:8888';
            $parts = parse_url($bridgeUrl);
            $host = $parts['host'] ?? 'localhost';
            $port = $parts['port'] ?? 8888;
            $listen = "$host:$port";

            $bridgePath = dirname(__DIR__, 2) . '/huella/bridge_router.php';
            // Start the bridge in the background on Windows using absolute PHP path
            $phpPath = 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe'; // Corregido a versión existente
            pclose(popen("start /B \"\" \"$phpPath\" -S $listen \"$bridgePath\"", "r"));
            
            // Wait a second for it to start
            sleep(1);
            
            if ($this->checkStatus()) {
                $_SESSION['success'] = "Puente iniciado exitosamente en $listen.";
            } else {
                $_SESSION['error'] = "No se pudo iniciar el puente o tardó mucho en responder. Verifique manualmente.";
            }
        } else {
            $_SESSION['info'] = "El puente ya se encuentra en ejecución.";
        }
        
        header("Location: index.php?action=puente");
        exit();
    }

    public function stop() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido. Use POST.';
            header("Location: index.php?action=puente");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Token CSRF inválido o expirado.';
            header("Location: index.php?action=puente");
            exit();
        }

        $bridgeUrl = getenv('BRIDGE_LOCAL_URL') ?: 'http://localhost:8888';
        $parts = parse_url($bridgeUrl);
        $port = $parts['port'] ?? 8888;

        // Find the process ID listening on the configured port and kill it
        $output = shell_exec("netstat -ano | findstr :$port");
        if ($output) {
            $lines = explode("\n", trim($output));
            $killed = false;
            foreach ($lines as $line) {
                if (strpos($line, 'LISTENING') !== false) {
                    $parts = preg_split('/\s+/', trim($line));
                    $pid = end($parts);
                    if (is_numeric($pid) && $pid > 0) {
                        shell_exec("taskkill /F /PID $pid");
                        $killed = true;
                    }
                }
            }
            if ($killed) {
                $_SESSION['success'] = "El puente ha sido detenido exitosamente.";
            } else {
                $_SESSION['error'] = "No se pudo encontrar el proceso para detener el puente.";
            }
        } else {
            $_SESSION['info'] = "El puente no parece estar en ejecución.";
        }
        
        header("Location: index.php?action=puente");
        exit();
    }
    
    public function clearLogs() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header("Location: index.php?action=puente");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Token CSRF inválido.';
            header("Location: index.php?action=puente");
            exit();
        }

        $logFile = dirname(__DIR__, 2) . '/logs/bridge_router.log';
        $errorLogFile = dirname(__DIR__, 2) . '/logs/bridge_error.log';
        
        if (file_exists($logFile)) file_put_contents($logFile, '');
        if (file_exists($errorLogFile)) file_put_contents($errorLogFile, '');
        
        $_SESSION['success'] = "Logs limpiados exitosamente.";
        header("Location: index.php?action=puente");
        exit();
    }

    private function checkStatus() {
        $bridgeUrl = getenv('BRIDGE_LOCAL_URL') ?: 'http://localhost:8888';
        $parts = parse_url($bridgeUrl);
        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? 8888;

        $fp = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }
    
    private function getLogs() {
        $logFile = dirname(__DIR__, 2) . '/logs/bridge_router.log';
        $logs = [];
        if (file_exists($logFile)) {
            // Read the last 50 lines
            $file = file($logFile);
            $logs = array_slice($file, -50);
        }
        return array_reverse($logs);
    }
}
?>
