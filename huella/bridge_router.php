<?php
// huella/bridge_router.php
// Este script actúa como un puente para el lector Hikvision DS-K1F820-F
// Debe ejecutarse con: php -S localhost:8888 bridge_router.php

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$path = ltrim($path, '/');

// Log de depuración
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
$logFile = $logDir . '/bridge_router.log';

$debugInfo = [
    'time' => date('Y-m-d H:i:s'),
    'path' => $path,
    'method' => $_SERVER['REQUEST_METHOD'],
    'params' => $_GET
];
file_put_contents($logFile, json_encode($debugInfo) . "\n", FILE_APPEND);

$pythonPath = "C:\\laragon\\bin\\python\\python-3.10\\python.exe";
if (!file_exists($pythonPath)) {
    $pythonPath = "python"; // Fallback to global path
}

$scriptPath = __DIR__ . DIRECTORY_SEPARATOR . "bridge_python.py";

switch ($path) {
    case 'capture':
        // Modo Enrolamiento (desde frontend)
        $times = intval($_GET['times'] ?? (isset($_GET['mode']) && $_GET['mode'] === 'login' ? 1 : 0));
        $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " enroll --times " . $times;
        $output = shell_exec($cmd);
        file_put_contents($logFile, "Enroll Output: $output\n", FILE_APPEND);
        echo $output;
        break;
        
    case 'batch_match':
        $input_json = file_get_contents('php://input');
        $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " batch_match";
        
        $descriptorspec = array(
           0 => array("pipe", "r"),  # stdin
           1 => array("pipe", "w"),  # stdout
           2 => array("file", $logDir . "/bridge_error.log", "a") # stderr
        );
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], $input_json);
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
            
            file_put_contents($logFile, "Batch Match Output: $output\n", FILE_APPEND);
            echo $output;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to start python process']);
        }
        break;

    case 'verify':
        // Modo Verificación o Match
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $cmd = "";
        
        $t1 = $input['template1'] ?? ($_POST['template1'] ?? '');
        $t2 = $input['template2'] ?? ($_POST['template2'] ?? '');
        $t_single = $input['template'] ?? ($_POST['template'] ?? '');
        
        if (!empty($t1) && !empty($t2)) {
            // Comparación de dos templates (Match) - Usado por AuthController
            $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " match --template " . escapeshellarg($t1) . " --template2 " . escapeshellarg($t2);
        } elseif (!empty($t_single)) {
            // Capturar y comparar (Verify) - Para verificación directa
            $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " verify --template " . escapeshellarg($t_single);
        }
        
        if (!empty($cmd)) {
            $output = shell_exec($cmd);
            file_put_contents($logFile, "Verify/Match Output: $output\n", FILE_APPEND);
            
            $data = json_decode($output, true);
            if ($data && $data['status'] === 'success') {
                // Compatibilidad con AuthController que espera campo 'score'
                if (isset($data['match'])) {
                    $data['score'] = $data['match'] ? 100 : 0;
                }
                echo json_encode($data);
            } else {
                echo $output ?: json_encode(['status' => 'error', 'message' => 'No output from bridge']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros de huella (se requiere template1 y template2, o solo template)']);
        }
        break;

    default:
        // Si no coincide con ninguna acción específica, intentar responder success para pings
        if (empty($path)) {
            echo json_encode(['status' => 'online', 'bridge' => 'hikvision-python-sdk']);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Acción desconocida: $path"]);
        }
        break;
}
