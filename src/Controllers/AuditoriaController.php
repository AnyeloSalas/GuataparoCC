<?php
require_once __DIR__ . '/AdminController.php';

class AuditoriaController extends AdminController {
    private $auditoria;

    public function __construct() {
        parent::__construct(); // Auth & DB
        // Determine if we need specific roles? 
        // Index is viewable by authorized users (maybe just admins?)
        // Limpiar is definitely admin only.
        
        $this->auditoria = new AuditoriaHuella($this->db);
    }

    public function index() {
        // Obtener parámetros de filtro
        $tipo = $_GET['tipo'] ?? 'todos';
        $limit = $_GET['limit'] ?? 100;
        
        // Obtener logs según filtro
        if ($tipo === 'todos') {
            $stmt = $this->auditoria->read($limit);
        } else {
            $stmt = $this->auditoria->readByTipo($tipo, $limit);
        }
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener estadísticas
        $estadisticas = $this->auditoria->getEstadisticas();
        
        require_once PROJECT_ROOT . '/src/Views/auditoria/index.php';
    }

    public function limpiar() {
        $this->verifyRole('Administrador'); // Only admins can clean logs

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                header("Location: index.php?action=auditoria&error=invalid_token");
                exit();
            }
            require_once __DIR__ . '/../Models/Validator.php';
            
            $dias = isset($_POST['dias']) ? (int)Validator::clean($_POST['dias']) : 90;
            
            // Ensure minimum days to prevent accidental full wipe
            if ($dias < 30) $dias = 30;
            
            $eliminados = $this->auditoria->limpiarAntiguos($dias);
            
            header("Location: index.php?action=auditoria&success=limpiar&eliminados=" . $eliminados);
            exit();
        }
    }

    public function exportar() {
        $this->verifyRole('Administrador');

        $tipo = $_GET['tipo'] ?? 'todos';
        $limit = $_GET['limit'] ?? 100;
        $formato = $_GET['formato'] ?? 'print';

        if ($tipo === 'todos') {
            $stmt = $this->auditoria->read($limit);
        } else {
            $stmt = $this->auditoria->readByTipo($tipo, $limit);
        }
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=auditoria_huellas_' . date('Ymd_His') . '.csv');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            
            fputcsv($output, ['REPORTE DE AUDITORIA DE HUELLAS DACTILARES'], ';');
            fputcsv($output, ['Filtro Tipo', $tipo], ';');
            fputcsv($output, ['Limite', $limit], ';');
            fputcsv($output, ['Fecha Exportacion', date('d/m/Y H:i:s')], ';');
            fputcsv($output, [], ';');
            
            fputcsv($output, ['Fecha / Hora', 'Evento', 'Usuario', 'Cedula', 'Similitud (%)', 'Direccion IP', 'Detalles'], ';');
            
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['fecha_hora'],
                    strtoupper(str_replace('_', ' ', $log['tipo_evento'])),
                    $log['usuario'] ?? 'Sistema',
                    $log['cedula'] ?? '-',
                    $log['similitud_porcentaje'] ? number_format($log['similitud_porcentaje'], 1) : '-',
                    $log['ip_address'],
                    $log['detalles']
                ], ';');
            }
            fclose($output);
            exit();
        } else if ($formato === 'pdf' || $formato === 'print') {
            require_once __DIR__ . '/../Views/auditoria/exportar.php';
        }
    }
}
?>
