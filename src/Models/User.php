<?php

class User {
    private $conn;
    private $table_name = "usuarios";

    public $usuario;
    public $email;
    public $contrasena;
    public $tipousuario;
    public $foto;
    public $puestotrabajo; // String representation
    public $puesto_trabajo_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT u.usuario, u.contrasena, tu.nombre as tipousuario, u.foto, pt.nombre as puestotrabajo, u.bloqueado 
                  FROM " . $this->table_name . " u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  WHERE u.usuario = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password using bcrypt or fallback to MD5 (and rehash)
            $login_success = false;
            
            if (password_verify($password, $row['contrasena'])) {
                $login_success = true;
            } elseif ($row['contrasena'] === md5($password)) {
                // Migrate MD5 to bcrypt
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE " . $this->table_name . " SET contrasena = ? WHERE usuario = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $newHash);
                $update_stmt->bindParam(2, $row['usuario']);
                $update_stmt->execute();
                
                $login_success = true;
            }

            if ($login_success) {
                if ($row['bloqueado'] == 1) {
                    return 'blocked';
                }
                $this->usuario = $row['usuario'];
                $this->tipousuario = $row['tipousuario'];
                $this->foto = $row['foto'];
                $this->puestotrabajo = $row['puestotrabajo'];
                return true;
            }
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT u.usuario, u.email, tu.nombre as tipousuario, u.bloqueado 
                  FROM " . $this->table_name . " u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  WHERE u.email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->usuario = $row['usuario'];
            $this->email = $row['email'];
            $this->tipousuario = $row['tipousuario'];
            return true;
        }
        return false;
    }

    public function updatePassword($username, $newPassword) {
        $query = "UPDATE " . $this->table_name . " SET contrasena = ? WHERE usuario = ?";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bindParam(1, $hashedPassword);
        $stmt->bindParam(2, $username);
        return $stmt->execute();
    }
}
?>
