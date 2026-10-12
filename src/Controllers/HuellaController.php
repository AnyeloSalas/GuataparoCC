<?php

class HuellaController {
    private $db;
    private $huella;

    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->huella = new Huella($this->db);
    }

    public function save() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // This endpoint receives the fingerprint template from the frontend
        header('Content-Type: application/json');
        
        // Verificación de autenticación y rol
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado. Debe iniciar sesión.']);
            exit;
        }
        $tipoUsuario = strtolower($_SESSION['tipousuario'] ?? '');
        if ($tipoUsuario !== 'administrador' && $tipoUsuario !== 'operador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere rol administrativo para enrolar huellas.']);
            exit;
        }
        
        // Log file for debugging
        $logFile = __DIR__ . '/../../logs/huella_debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Aceptar datos tanto en JSON como en form-urlencoded
                $cedula = null;
                $template = null;
                $template2 = null;
                
                // Intentar leer como JSON primero
                $json_data = json_decode(file_get_contents("php://input"), true);
                if ($json_data && isset($json_data['cedula']) && isset($json_data['template'])) {
                    $cedula = $json_data['cedula'];
                    $template = $json_data['template'];
                    $template2 = $json_data['template2'] ?? null;
                    if (isset($json_data['type'])) {
                        $type = $json_data['type'];
                    }
                }
                // Si no es JSON, leer como POST normal
                elseif (isset($_POST['cedula']) && isset($_POST['template'])) {
                    $cedula = $_POST['cedula'];
                    $template = $_POST['template'];
                    $template2 = $_POST['template2'] ?? null;
                    if (isset($_POST['type'])) {
                        $type = $_POST['type'];
                    }
                }
                
                // LOG: Datos recibidos
                @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Datos recibidos: cedula=$cedula, type=$type, template_length=" . strlen($template) . "\n", FILE_APPEND);
                
                // VALIDACIÓN CRÍTICA: Rechazar templates muy cortos (datos corruptos)
                // Templates válidos deberían tener mínimo 100 bytes (idealmente 500+)
                if (strlen($template) < 100) {
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Template 1 muy corto (" . strlen($template) . " bytes). RECHAZADO.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: Huella 1 inválida (template muy corto: ' . strlen($template) . ' bytes). Esto indica que el sensor no capturó correctamente. Por favor:\n1. Verifique que run_cpp_bridge.bat esté ejecutándose\n2. Verifique que el sensor esté conectado\n3. Intente capturar nuevamente'
                    ]);
                    exit;
                }
                
                if (!$template2 || strlen($template2) < 100) {
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Template 2 muy corto o faltante. RECHAZADO.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: Debe capturar OBLIGATORIAMENTE dos huellas diferentes (ej. Índice Derecho e Índice Izquierdo) por motivos de seguridad.'
                    ]);
                    exit;
                }
                
                if ($template === $template2) {
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Las dos huellas capturadas son idénticas. RECHAZADO.\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: Ha capturado el MISMO dedo dos veces. Por favor asegúrese de usar un dedo diferente para la segunda huella.'
                    ]);
                    exit;
                }
                
                if ($cedula && $template) {
                    $table = ($type === 'socio') ? 'personas' : 'usuarios';
                    
                    // LOG: Tabla seleccionada
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Tabla seleccionada: $table\n", FILE_APPEND);

                    require_once __DIR__ . '/../Models/CryptoHelper.php';

                    $identifierField = ($type === 'socio') ? 'cedula' : 'usuario';

                    // Verificar si ya existe una huella para este usuario/socio
                    $checkQuery = "SELECT huella_template FROM $table WHERE $identifierField = :identifier";
                    $checkStmt = $this->db->prepare($checkQuery);
                    $checkStmt->bindParam(':identifier', $cedula);
                    $checkStmt->execute();
                    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // LOG: Resultado de búsqueda
                    if ($existingUser) {
                        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usuario encontrado en $table\n", FILE_APPEND);
                    } else {
                        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usuario NO encontrado en $table con cedula=$cedula\n", FILE_APPEND);
                    }
                    
                    $esActualizacion = ($existingUser && !empty($existingUser['huella_template']));
                    
                    // Guardar el template directamente en la tabla correspondiente
                    $query = "UPDATE $table SET huella_template = :template, huella_template_2 = :template2 WHERE $identifierField = :identifier";
                    $stmt = $this->db->prepare($query);
                    
                    // Encriptar el template con AES-256 para máxima seguridad en BD
                    $templateToStore = CryptoHelper::encrypt($template);
                    $templateToStore2 = CryptoHelper::encrypt($template2);
                    
                    $stmt->bindParam(':template', $templateToStore);
                    $stmt->bindParam(':template2', $templateToStore2);
                    $stmt->bindParam(':identifier', $cedula);
                    
                    if ($stmt->execute()) {
                        $rowCount = $stmt->rowCount();
                        
                        // LOG: Resultado de UPDATE
                        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - UPDATE ejecutado, filas afectadas: $rowCount\n", FILE_APPEND);
                        
                        // Verificar que se actualizó al menos un registro
                        if ($rowCount > 0) {
                            // Registrar en auditoría
                            $auditoria = new AuditoriaHuella($this->db);
                            $tipoEvento = $esActualizacion ? 'actualizacion' : 'registro';
                            $mensaje = $esActualizacion ? 'Huella actualizada' : 'Huella registrada por primera vez';
                            
                            $auditoria->registrar(
                                $tipoEvento,
                                null, // No tenemos el nombre de usuario aquí, solo cédula
                                $cedula,
                                null, // No hay similitud en registro
                                $mensaje
                            );
                            
                            @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Huella guardada exitosamente\n", FILE_APPEND);
                            
                            echo json_encode([
                                'success' => true,
                                'message' => 'Huella guardada exitosamente para la cédula: ' . $cedula
                            ]);
                        } else {
                            @file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: No se encontró registro con cedula=$cedula en $table\n", FILE_APPEND);
                            
                            echo json_encode([
                                'success' => false,
                                'message' => 'No se encontró ningún registro con la cédula: ' . $cedula . ' en la tabla ' . $table
                            ]);
                        }
                    } else {
                        @file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Fallo al ejecutar UPDATE\n", FILE_APPEND);
                        
                        echo json_encode([
                            'success' => false,
                            'message' => 'Error al ejecutar la consulta de actualización'
                        ]);
                    }
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: Datos incompletos\n", FILE_APPEND);
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Datos incompletos. Se requiere cedula y template.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido. Use POST.'
                ]);
            }
        } catch (Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function verify() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // This endpoint verifies a fingerprint against stored template
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado. Debe iniciar sesión.']);
            exit;
        }
        
        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $data = json_decode(file_get_contents("php://input"), true);
                
                if (isset($data['cedula'])) {
                    // Obtenemos la huella desencriptada de la BD
                    $storedTemplateB64 = $this->huella->getTemplateOnly($data['cedula']);
                    
                    if ($storedTemplateB64) {
                        echo json_encode([
                            'success' => true,
                            'stored_template' => $storedTemplateB64
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'El socio no tiene una huella registrada.'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Datos incompletos'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function getAll() {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');
        
        // Solo administradores pueden consultar todos los templates biométricos
        if (!isset($_SESSION['usuario']) || strtolower($_SESSION['tipousuario'] ?? '') !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Requiere rol de Administrador.']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../Models/CryptoHelper.php';
            $query = "SELECT cedula, huella_template, huella_template_2 FROM personas WHERE (huella_template IS NOT NULL AND huella_template != '') OR (huella_template_2 IS NOT NULL AND huella_template_2 != '')";
            $stmt = $this->db->query($query);
            $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $templates = [];
            foreach ($socios as $s) {
                if (!empty($s['huella_template'])) {
                    $templates[] = [
                        'cedula' => $s['cedula'],
                        'dedo' => 1,
                        'template' => CryptoHelper::decrypt($s['huella_template'])
                    ];
                }
                if (!empty($s['huella_template_2'])) {
                    $templates[] = [
                        'cedula' => $s['cedula'],
                        'dedo' => 2,
                        'template' => CryptoHelper::decrypt($s['huella_template_2'])
                    ];
                }
            }
            echo json_encode(['success' => true, 'templates' => $templates]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function delete() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        // Solo administradores pueden eliminar huellas
        if (!isset($_SESSION['usuario']) || strtolower($_SESSION['tipousuario'] ?? '') !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado. Requiere rol de Administrador.']);
            exit;
        }
        
        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                try {
                    CsrfMiddleware::verify();
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
                    exit;
                }
                
                $cedula = $_POST['cedula'] ?? '';
                $type = $_POST['type'] ?? 'usuario';
                
                if (empty($cedula)) {
                    echo json_encode(['success' => false, 'message' => 'Cédula requerida']);
                    exit;
                }

                $table = ($type === 'socio') ? 'personas' : 'usuarios';
                $identifierField = ($type === 'socio') ? 'cedula' : 'usuario';

                // Eliminar la huella (establecer a NULL ambas)
                $query = "UPDATE $table SET huella_template = NULL, huella_template_2 = NULL WHERE $identifierField = :identifier";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':identifier', $cedula);
                
                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        // Registrar en auditoría
                        $auditoria = new AuditoriaHuella($this->db);
                        $auditoria->registrar(
                            'eliminacion',
                            null,
                            $cedula,
                            null,
                            'Huella eliminada del sistema'
                        );
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Huella eliminada exitosamente'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'No se encontró ningún usuario con esa cédula'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al ejecutar la consulta'
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Método no permitido. Use POST.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
?>
