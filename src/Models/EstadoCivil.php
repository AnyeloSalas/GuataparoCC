<?php
// src/Models/EstadoCivil.php

class EstadoCivil {
    private $conn;
    private $table_name = "estado_civil";

    public $id;
    public $nombre;
    public $genero;
    public $descripcion;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($offset = null, $limit = null) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC";
        if ($offset !== null && $limit !== null) {
            $query .= " LIMIT :offset, :limit";
        }
        $stmt = $this->conn->prepare($query);
        if ($offset !== null && $limit !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt;
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
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
            $this->genero = $row['genero'];
            $this->descripcion = $row['descripcion'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    public function existsByName($nombre) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE LOWER(nombre) = LOWER(?) LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nombre]);
        return $stmt->rowCount() > 0;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, genero=:genero, descripcion=:descripcion, activo=:activo";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->genero = $this->genero ?? 'N';
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->activo = $this->activo ?? 1;

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":genero", $this->genero);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":activo", $this->activo);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en EstadoCivil::create: " . $e->getMessage());
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre=:nombre, genero=:genero, descripcion=:descripcion, activo=:activo 
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->genero = htmlspecialchars(strip_tags($this->genero));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->activo = htmlspecialchars(strip_tags($this->activo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":genero", $this->genero);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
