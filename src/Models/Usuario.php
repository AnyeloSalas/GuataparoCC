<?php

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $usuario;
    public $email;
    public $contrasena;
    public $foto;
    public $tipousuario; // String representing the name
    public $tipo_usuario_id;
    public $puestotrabajo; // String representing the name
    public $puesto_trabajo_id;
    public $bloqueado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($offset = null, $limit = null) {
        $query = "SELECT u.usuario, u.email, tu.nombre as tipousuario, u.tipo_usuario_id, pt.nombre as puestotrabajo, u.puesto_trabajo_id, u.bloqueado 
                  FROM " . $this->table_name . " u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  ORDER BY u.usuario ASC";
                  
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

    public function readOne() {
        $query = "SELECT u.usuario, u.email, u.contrasena, u.foto, tu.nombre as tipousuario, u.tipo_usuario_id, pt.nombre as puestotrabajo, u.puesto_trabajo_id, u.bloqueado 
                  FROM " . $this->table_name . " u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  WHERE u.usuario = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->usuario);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->email = $row['email'];
            $this->contrasena = $row['contrasena'];
            $this->foto = $row['foto'];
            $this->tipousuario = $row['tipousuario'];
            $this->tipo_usuario_id = $row['tipo_usuario_id'];
            $this->puestotrabajo = $row['puestotrabajo'];
            $this->puesto_trabajo_id = $row['puesto_trabajo_id'];
            $this->bloqueado = $row['bloqueado'];
            return true;
        }
        return false;
    }

    public function existsByUsername($username) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET usuario=:usuario, email=:email, contrasena=:contrasena, tipo_usuario_id=:tipo_usuario_id, puesto_trabajo_id=:puesto_trabajo_id, bloqueado=:bloqueado";
        $stmt = $this->conn->prepare($query);

        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->email = !empty($this->email) ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->tipo_usuario_id = htmlspecialchars(strip_tags($this->tipo_usuario_id));
        $this->puesto_trabajo_id = !empty($this->puesto_trabajo_id) ? htmlspecialchars(strip_tags($this->puesto_trabajo_id)) : null;
        $this->bloqueado = htmlspecialchars(strip_tags($this->bloqueado));

        // Hash password with bcrypt
        $hashed_password = password_hash($this->contrasena, PASSWORD_DEFAULT);

        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":contrasena", $hashed_password);
        $stmt->bindParam(":tipo_usuario_id", $this->tipo_usuario_id);
        
        if($this->puesto_trabajo_id === null || $this->puesto_trabajo_id === '') {
            $stmt->bindValue(":puesto_trabajo_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":puesto_trabajo_id", $this->puesto_trabajo_id);
        }
        
        $stmt->bindParam(":bloqueado", $this->bloqueado);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET email=:email, tipo_usuario_id=:tipo_usuario_id, puesto_trabajo_id=:puesto_trabajo_id, bloqueado=:bloqueado WHERE usuario=:usuario";
        $stmt = $this->conn->prepare($query);

        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->email = !empty($this->email) ? htmlspecialchars(strip_tags($this->email)) : null;
        $this->tipo_usuario_id = htmlspecialchars(strip_tags($this->tipo_usuario_id));
        $this->puesto_trabajo_id = !empty($this->puesto_trabajo_id) ? htmlspecialchars(strip_tags($this->puesto_trabajo_id)) : null;
        $this->bloqueado = htmlspecialchars(strip_tags($this->bloqueado));

        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":tipo_usuario_id", $this->tipo_usuario_id);
        
        if($this->puesto_trabajo_id === null || $this->puesto_trabajo_id === '') {
            $stmt->bindValue(":puesto_trabajo_id", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":puesto_trabajo_id", $this->puesto_trabajo_id);
        }
        
        $stmt->bindParam(":bloqueado", $this->bloqueado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " SET contrasena=:contrasena WHERE usuario=:usuario";
        $stmt = $this->conn->prepare($query);

        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $hashed_password = password_hash($this->contrasena, PASSWORD_DEFAULT);

        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":contrasena", $hashed_password);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE usuario = ?";
        $stmt = $this->conn->prepare($query);
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $stmt->bindParam(1, $this->usuario);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($keyword) {
        $query = "SELECT u.usuario, tu.nombre as tipousuario, pt.nombre as puestotrabajo 
                  FROM " . $this->table_name . " u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  WHERE u.usuario LIKE ? OR tu.nombre LIKE ? OR pt.nombre LIKE ?
                  ORDER BY u.usuario ASC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function verifyPassword($password) {
        if (password_verify($password, $this->contrasena)) {
            if (password_needs_rehash($this->contrasena, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $this->conn->prepare("UPDATE " . $this->table_name . " SET contrasena = :pass WHERE usuario = :user");
                $up->execute([':pass' => $newHash, ':user' => $this->usuario]);
                $this->contrasena = $newHash;
            }
            return true;
        } elseif ($this->contrasena === md5($password)) {
            // Upgrade legacy MD5 hash to Bcrypt transparently on login
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = $this->conn->prepare("UPDATE " . $this->table_name . " SET contrasena = :pass WHERE usuario = :user");
            $up->execute([':pass' => $newHash, ':user' => $this->usuario]);
            $this->contrasena = $newHash;
            return true;
        }
        return false;
    }


    public function toggleBlock($status) {
        // Option 1: Using the specific stored procedure (if preferred by user)
        // $query = "CALL sp_bloquear_usuario(:usuario, :bloqueado)";
        
        // Option 2: Direct update (Simple and effective given we just updated the table structure)
        $query = "UPDATE " . $this->table_name . " SET bloqueado = :bloqueado WHERE usuario = :usuario";
        
        $stmt = $this->conn->prepare($query);
        
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->bloqueado = htmlspecialchars(strip_tags($status));
        
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":bloqueado", $this->bloqueado);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
