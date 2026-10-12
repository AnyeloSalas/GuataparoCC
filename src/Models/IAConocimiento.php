<?php

class IAConocimiento {
    private $conn;
    private $table_name = "ia_conocimiento";

    public $id;
    public $palabras_clave;
    public $respuesta;
    public $categoria;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
        $this->initTable();
    }

    // Inicializar tabla si no existe
    private function initTable() {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            palabras_clave VARCHAR(255) NOT NULL,
            respuesta TEXT NOT NULL,
            categoria VARCHAR(50) DEFAULT 'General',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        try {
            $this->conn->exec($sql);
        } catch(PDOException $e) {
            // Silenciar error si ya existe o problemas de permisos, 
            // el error real saltará en las consultas si falla
        }
    }

    // Leer todos los conocimientos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear nuevo conocimiento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET palabras_clave=:palabras_clave, respuesta=:respuesta, categoria=:categoria";

        $stmt = $this->conn->prepare($query);

        $this->palabras_clave = htmlspecialchars(strip_tags($this->palabras_clave));
        $this->respuesta = htmlspecialchars(strip_tags($this->respuesta));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));

        $stmt->bindParam(":palabras_clave", $this->palabras_clave);
        $stmt->bindParam(":respuesta", $this->respuesta);
        $stmt->bindParam(":categoria", $this->categoria);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar conocimiento
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar respuesta basada en palabras clave
    public function buscarRespuesta($pregunta) {
        // Normalizar pregunta
        $pregunta = strtolower(strip_tags($pregunta));
        
        // Buscamos todas las posibles coincidencias donde la palabra clave esté contenida en la pregunta
        // NOTA: Invertimos el LIKE para buscar si 'palabra_clave' es parte de 'pregunta'
        $query = "SELECT palabras_clave, respuesta FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $mejor_coincidencia = null;
        $longitud_maxima = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $keywords = strtolower($row['palabras_clave']);
            
            // Verificamos si las palabras clave están en la pregunta
            if (stripos($pregunta, $keywords) !== false) {
                // Si encontramos una coincidencia, verificamos si es más específica (más larga)
                $longitud = strlen($keywords);
                if ($longitud > $longitud_maxima) {
                    $longitud_maxima = $longitud;
                    $mejor_coincidencia = $row['respuesta'];
                }
            }
        }
        
        return $mejor_coincidencia;
    }
}
?>
