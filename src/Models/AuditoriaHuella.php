<?php

class AuditoriaHuella {
    private $conn;
    private $table_name = "auditoria_huellas";

    public $id;
    public $fecha_hora;
    public $tipo_evento;
    public $usuario;
    public $cedula;
    public $similitud_porcentaje;
    public $ip_address;
    public $user_agent;
    public $detalles;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar un evento de auditoría
    public function registrar($tipo_evento, $usuario = null, $cedula = null, $similitud = null, $detalles = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET tipo_evento = :tipo_evento,
                      usuario = :usuario,
                      cedula = :cedula,
                      similitud_porcentaje = :similitud,
                      ip_address = :ip,
                      user_agent = :user_agent,
                      detalles = :detalles";

        $stmt = $this->conn->prepare($query);

        // Obtener IP del cliente
        $ip = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';

        // Sanitizar datos
        $tipo_evento = htmlspecialchars(strip_tags($tipo_evento));
        $usuario = $usuario ? htmlspecialchars(strip_tags($usuario)) : null;
        $cedula = $cedula ? htmlspecialchars(strip_tags($cedula)) : null;
        $detalles = $detalles ? htmlspecialchars(strip_tags($detalles)) : null;

        $stmt->bindParam(":tipo_evento", $tipo_evento);
        $stmt->bindParam(":usuario", $usuario);
        $stmt->bindParam(":cedula", $cedula);
        $stmt->bindParam(":similitud", $similitud);
        $stmt->bindParam(":ip", $ip);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->bindParam(":detalles", $detalles);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener todos los logs con paginación
    public function read($limit = 100, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY fecha_hora DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener logs por tipo de evento
    public function readByTipo($tipo_evento, $limit = 100) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tipo_evento = :tipo_evento
                  ORDER BY fecha_hora DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo_evento', $tipo_evento);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener logs por usuario
    public function readByUsuario($usuario, $limit = 100) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario = :usuario
                  ORDER BY fecha_hora DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener logs por rango de fechas
    public function readByFechas($fecha_inicio, $fecha_fin, $limit = 1000) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE fecha_hora BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY fecha_hora DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener estadísticas
    public function getEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total_eventos,
                    SUM(CASE WHEN tipo_evento = 'login_exitoso' THEN 1 ELSE 0 END) as logins_exitosos,
                    SUM(CASE WHEN tipo_evento = 'login_fallido' THEN 1 ELSE 0 END) as logins_fallidos,
                    SUM(CASE WHEN tipo_evento = 'registro' THEN 1 ELSE 0 END) as registros,
                    SUM(CASE WHEN tipo_evento = 'eliminacion' THEN 1 ELSE 0 END) as eliminaciones,
                    AVG(CASE WHEN similitud_porcentaje IS NOT NULL THEN similitud_porcentaje ELSE NULL END) as similitud_promedio
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener IP del cliente
    private function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocido';
        }
        return $ip;
    }

    // Limpiar logs antiguos (más de X días)
    public function limpiarAntiguos($dias = 90) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $stmt->rowCount();
        }
        return 0;
    }
}
?>
