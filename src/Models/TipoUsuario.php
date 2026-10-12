<?php

class TipoUsuario {
    private $conn;
    private $table_name = "tipos_usuario";

    public $id;
    public $nombre;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    public function readByNombre($nombre) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE nombre = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nombre);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    public function existsByName($nombre, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE nombre = ?";
        if ($excludeId) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nombre);
        if ($excludeId) {
            $stmt->bindParam(2, $excludeId);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre, activo=:activo";
            $stmt = $this->conn->prepare($query);

            $this->nombre = htmlspecialchars(strip_tags($this->nombre));
            $this->activo = $this->activo ?? 1;

            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":activo", $this->activo);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in TipoUsuario::create: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . " SET nombre=:nombre, activo=:activo WHERE id=:id";
            $stmt = $this->conn->prepare($query);

            $this->nombre = htmlspecialchars(strip_tags($this->nombre));
            $this->activo = htmlspecialchars(strip_tags($this->activo));
            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":activo", $this->activo);
            $stmt->bindParam(":id", $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in TipoUsuario::update: " . $e->getMessage());
            return false;
        }
    }

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
}
?>
