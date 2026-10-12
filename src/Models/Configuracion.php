<?php

class Configuracion {
    private $conn;
    private $table_name = "configuracion";

    public $id;
    public $clave;
    public $valor;
    public $descripcion;
    public $tipo;
    public $categoria;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all configuration settings
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY categoria, id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get configurations by category
    public function getByCategoria($categoria) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE categoria = ? ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categoria);
        $stmt->execute();
        return $stmt;
    }

    // Get all categories
    public function getCategorias() {
        $query = "SELECT DISTINCT categoria FROM " . $this->table_name . " ORDER BY categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get a single setting by key
    public function get($key) {
        $query = "SELECT valor FROM " . $this->table_name . " WHERE clave = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['valor'];
        }
        return null;
    }

    // Get full configuration object by key
    public function getFull($key) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE clave = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Update a setting
    public function update($key, $value) {
        // Get the configuration to validate type
        $config = $this->getFull($key);
        
        if (!$config) {
            return false;
        }

        // Validate based on type
        if (!$this->validateValue($value, $config['tipo'])) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET valor = :valor WHERE clave = :clave";
        $stmt = $this->conn->prepare($query);

        $value = htmlspecialchars(strip_tags($value));
        $key = htmlspecialchars(strip_tags($key));

        $stmt->bindParam(':valor', $value);
        $stmt->bindParam(':clave', $key);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Validate value based on type
    private function validateValue($value, $type) {
        switch ($type) {
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return in_array($value, ['0', '1', 0, 1, true, false]);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'color':
                return preg_match('/^#[0-9A-F]{6}$/i', $value);
            case 'text':
            case 'textarea':
            default:
                return true; // Text values are always valid
        }
    }

    // Helper methods for common configurations
    public function getSistemaNombre() {
        return $this->get('sistema_nombre') ?? 'Sistema Guataparo';
    }

    public function getSistemaEmail() {
        return $this->get('sistema_email') ?? 'contacto@guataparo.com';
    }

    public function getColorPrimario() {
        return $this->get('color_primario') ?? '#0d6efd';
    }

    public function getColorLetra() {
        return $this->get('color_letra') ?? '#858796';
    }

    public function isMantenimientoActivo() {
        return $this->get('mantenimiento_modo') == '1';
    }

    public function getSesionTimeout() {
        return intval($this->get('sesion_timeout') ?? 3600);
    }

    // Create a new configuration
    public function create($clave, $valor, $descripcion, $tipo = 'text', $categoria = 'general') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (clave, valor, descripcion, tipo, categoria) 
                  VALUES (:clave, :valor, :descripcion, :tipo, :categoria)";
        
        $stmt = $this->conn->prepare($query);

        $clave = htmlspecialchars(strip_tags($clave));
        $valor = htmlspecialchars(strip_tags($valor));
        $descripcion = htmlspecialchars(strip_tags($descripcion));
        $tipo = htmlspecialchars(strip_tags($tipo));
        $categoria = htmlspecialchars(strip_tags($categoria));

        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':categoria', $categoria);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a configuration
    public function delete($clave) {
        $query = "DELETE FROM " . $this->table_name . " WHERE clave = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $clave);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
