<?php
// src/Models/Empresa.php

class Empresa {
    private $conn;
    private $table_name = "empresas";

    public $id_empresa;
    public $rif_empresa;
    public $nombre_empresa;
    public $direccion_empresa;
    public $tipo_rif;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function read($offset = 0, $limit = null) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre_empresa ASC";
        if ($limit !== null) {
            $query .= " LIMIT :offset, :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_empresa = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_empresa);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_empresa = $row['id_empresa'];
            $this->rif_empresa = $row['rif_empresa'];
            $this->nombre_empresa = $row['nombre_empresa'];
            $this->direccion_empresa = $row['direccion_empresa'];
            $this->tipo_rif = $row['tipo_rif'];
            return true;
        }
        return false;
    }

    public function existsByName($nombre) {
        $query = "SELECT id_empresa FROM " . $this->table_name . " WHERE LOWER(nombre_empresa) = LOWER(?) LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nombre]);
        return $stmt->rowCount() > 0;
    }

    public function existsByRif($rif, $exclude_id = null) {
        $query = "SELECT id_empresa FROM " . $this->table_name . " WHERE LOWER(rif_empresa) = LOWER(?)";
        if ($exclude_id !== null) {
            $query .= " AND id_empresa != ?";
        }
        $query .= " LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        if ($exclude_id !== null) {
            $stmt->execute([$rif, $exclude_id]);
        } else {
            $stmt->execute([$rif]);
        }
        return $stmt->rowCount() > 0;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET rif_empresa=:rif, nombre_empresa=:nombre, direccion_empresa=:direccion, tipo_rif=:tipo";

        $stmt = $this->conn->prepare($query);

        $this->rif_empresa = htmlspecialchars(strip_tags($this->rif_empresa));
        $this->nombre_empresa = htmlspecialchars(strip_tags($this->nombre_empresa));
        $this->direccion_empresa = htmlspecialchars(strip_tags($this->direccion_empresa));
        $this->tipo_rif = htmlspecialchars(strip_tags($this->tipo_rif));

        $stmt->bindParam(":rif", $this->rif_empresa);
        $stmt->bindParam(":nombre", $this->nombre_empresa);
        $stmt->bindParam(":direccion", $this->direccion_empresa);
        $stmt->bindParam(":tipo", $this->tipo_rif);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en Empresa::create: " . $e->getMessage());
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET rif_empresa=:rif, nombre_empresa=:nombre, direccion_empresa=:direccion, tipo_rif=:tipo
                  WHERE id_empresa=:id";

        $stmt = $this->conn->prepare($query);

        $this->rif_empresa = htmlspecialchars(strip_tags($this->rif_empresa));
        $this->nombre_empresa = htmlspecialchars(strip_tags($this->nombre_empresa));
        $this->direccion_empresa = htmlspecialchars(strip_tags($this->direccion_empresa));
        $this->tipo_rif = htmlspecialchars(strip_tags($this->tipo_rif));
        $this->id_empresa = htmlspecialchars(strip_tags($this->id_empresa));

        $stmt->bindParam(":rif", $this->rif_empresa);
        $stmt->bindParam(":nombre", $this->nombre_empresa);
        $stmt->bindParam(":direccion", $this->direccion_empresa);
        $stmt->bindParam(":tipo", $this->tipo_rif);
        $stmt->bindParam(":id", $this->id_empresa);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_empresa = ?";
        $stmt = $this->conn->prepare($query);
        $this->id_empresa = htmlspecialchars(strip_tags($this->id_empresa));
        $stmt->bindParam(1, $this->id_empresa);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
