<?php

class Huella {
    private $conn;
    private $table_name = "huellas";

    public $id;
    public $cedula;
    public $template;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene el template desencriptado directamente (para validación en cliente)
     */
    public function getTemplateOnly($cedula) {
        return $this->getTemplateFromDB($cedula);
    }

    public function verify($cedula, $capturedTemplateB64) {
        $storedTemplateB64 = $this->getTemplateFromDB($cedula);
        
        if (!$storedTemplateB64) {
            return false;
        }

        // Si por alguna razón recibimos binario, lo convertimos, 
        // pero la idea es que ya venga en Base64 para evitar corrupción
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $capturedTemplateB64)) {
            $capturedTemplateB64 = base64_encode($capturedTemplateB64);
        }
        
        // Llamar al Bridge para comparar
        return $this->matchWithBridge($capturedTemplateB64, $storedTemplateB64);
    }
    private function getTemplateFromDB($cedula) {
        $templates = [];
        // 1. Intentar buscar en usuarios - Solo si no están bloqueados
        try {
            $query = "SELECT huella_template, huella_template_2 FROM usuarios 
                      WHERE usuario = :cedula 
                      AND (bloqueado IS NULL OR bloqueado = 0)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['huella_template'])) $templates[] = $row['huella_template'];
                if (!empty($row['huella_template_2'])) $templates[] = $row['huella_template_2'];
            }
        } catch (PDOException $e) {
            // Error en query usuarios
        }

        // 2. Intentar buscar en personas (socios) - Solo si tienen al menos una acción ACTIVA
        if (empty($templates)) {
            try {
                $query = "SELECT p.huella_template, p.huella_template_2
                          FROM personas p
                          JOIN acciones a ON p.id = a.persona_id
                          WHERE p.cedula = :cedula 
                          AND a.estado = 'ACTIVO'
                          LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':cedula', $cedula);
                $stmt->execute();
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['huella_template'])) $templates[] = $row['huella_template'];
                    if (!empty($row['huella_template_2'])) $templates[] = $row['huella_template_2'];
                }
            } catch (PDOException $e) {
                // Error en query personas
            }
        }

        if (empty($templates)) {
            return null;
        }

        require_once __DIR__ . '/CryptoHelper.php';
        $decrypted = [];
        foreach ($templates as $t) {
            $decrypted[] = CryptoHelper::decrypt($t);
        }
        return $decrypted;
    }

    private function matchWithBridge($t1_b64, $t2_b64) {
        $url = "http://127.0.0.1:8888/verify";
        $payload = json_encode(['template1' => $t1_b64, 'template2' => $t2_b64]);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $payload,
                'timeout' => 5,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($options);
        
        // Usamos @ para evitar que un error de red lance un Warning que rompa el JSON
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            // Si falló 127.0.0.1, probamos localhost como último recurso
            $url = "http://localhost:8888/verify";
            $result = @file_get_contents($url, false, $context);
        }

        if ($result === FALSE) {
            return false;
        }

        $response = json_decode($result, true);
        return (isset($response['match']) && $response['match'] === true);
    }

    public function create() {
        // ... (existing code, but maybe update table?)
        // HuellaController handles saving now directly to usuarios/personas
        // but let's keep this for compatibility if used elsewhere
        $query = "INSERT INTO " . $this->table_name . " SET cedula=:cedula, template=:template";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cedula", $this->cedula);
        $stmt->bindParam(":template", $this->template);
        return $stmt->execute();
    }

    public function getByCedula($cedula) {
        $query = "SELECT id, cedula, template, created_at FROM " . $this->table_name . " WHERE cedula = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cedula);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->cedula = $row['cedula'];
            $this->template = $row['template'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function getAllWithUsers() {
        // Obtener la última huella de cada usuario que tenga huella
        $query = "SELECT h.template, u.usuario, tu.nombre as tipousuario, u.foto 
                  FROM " . $this->table_name . " h
                  JOIN usuarios u ON h.cedula = u.usuario
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  WHERE h.id IN (
                      SELECT MAX(id) FROM " . $this->table_name . " GROUP BY cedula
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
