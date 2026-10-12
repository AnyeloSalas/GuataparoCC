<?php

class DocumentoController {
    private $documentosPath;
    
    public function __construct() {
        $this->documentosPath = PROJECT_ROOT . '/DOCUMENTOS';
    }
    
    /**
     * Lista todos los documentos disponibles
     */
    public function index() {
        $documentos = $this->getDocumentos();
        require_once PROJECT_ROOT . '/src/Views/documentos/index.php';
    }
    
    /**
     * Descarga un archivo específico
     */
    public function download() {
        $filename = isset($_GET['file']) ? $_GET['file'] : '';
        
        // Validar que el nombre de archivo no contenga rutas
        if (empty($filename) || strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Archivo inválido';
            exit;
        }
        
        $filepath = $this->documentosPath . '/' . $filename;
        
        // Verificar que el archivo existe
        if (!file_exists($filepath)) {
            header('HTTP/1.0 404 Not Found');
            echo 'Archivo no encontrado';
            exit;
        }
        
        // Obtener tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        // Configurar cabeceras para descarga
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // Leer y enviar el archivo
        readfile($filepath);
        exit;
    }
    
    /**
     * Obtiene la lista de documentos disponibles
     */
    private function getDocumentos() {
        $documentos = [];
        
        if (is_dir($this->documentosPath)) {
            $files = scandir($this->documentosPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($this->documentosPath . '/' . $file)) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $documentos[] = [
                        'nombre' => $file,
                        'extension' => $ext,
                        'tamano' => filesize($this->documentosPath . '/' . $file),
                        'icono' => $this->getIcono($ext)
                    ];
                }
            }
        }
        
        // Ordenar por nombre
        usort($documentos, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });
        
        return $documentos;
    }
    
    /**
     * Obtiene el ícono según la extensión del archivo
     */
    private function getIcono($ext) {
        $iconos = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'jpg' => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'png' => 'fa-file-image text-info',
            'zip' => 'fa-file-archive text-warning',
            'rar' => 'fa-file-archive text-warning'
        ];
        
        return isset($iconos[$ext]) ? $iconos[$ext] : 'fa-file text-secondary';
    }
    
    /**
     * Formatea el tamaño del archivo
     */
    public static function formatFileSize($bytes) {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
