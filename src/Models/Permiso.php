<?php
class Permiso {
    private $conn;
    public $tipousuario;
    public $modulo_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getTiposUsuario() {
        $query = "SELECT nombre as tipousuario FROM tipos_usuario WHERE UPPER(nombre) != 'SOCIO'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllModulos() {
        $query = "SELECT * FROM modulos WHERE activo = 1 ORDER BY categoria, orden";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getPermisosByRole($tipousuario) {
        $query = "SELECT modulo_id FROM permisos_tipousuario WHERE LOWER(tipousuario) = LOWER(:tipousuario)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipousuario', $tipousuario);
        $stmt->execute();
        
        $permisos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permisos[] = $row['modulo_id'];
        }
        return $permisos;
    }

    public function updatePermisos($tipousuario, $modulos) {
        try {
            $this->conn->beginTransaction();

            // Eliminar permisos actuales para ese rol
            $queryDelete = "DELETE FROM permisos_tipousuario WHERE LOWER(tipousuario) = LOWER(:tipousuario)";
            $stmtDel = $this->conn->prepare($queryDelete);
            $stmtDel->bindParam(':tipousuario', $tipousuario);
            $stmtDel->execute();

            // Insertar los nuevos permisos
            if (!empty($modulos) && is_array($modulos)) {
                $queryInsert = "INSERT INTO permisos_tipousuario (tipousuario, modulo_id) VALUES (:tipousuario, :modulo_id)";
                $stmtIns = $this->conn->prepare($queryInsert);
                
                foreach ($modulos as $modulo_id) {
                    $stmtIns->bindParam(':tipousuario', $tipousuario);
                    $stmtIns->bindParam(':modulo_id', $modulo_id);
                    $stmtIns->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function hasAccess($tipousuario, $accion) {
        // Administrador siempre tiene acceso total
        if (strtolower($tipousuario) === 'administrador') {
            return true;
        }

        // Obtener todos los módulos activos ordenados por longitud descendente
        // Esto garantiza que módulos compuestos como 'tipos_usuario', 'tipos_relacion', 'estado_civil',
        // 'puestos_trabajo', 'ia_dashboard' se reconozcan antes de recortar por el primer guion bajo
        $stmtMod = $this->conn->query("SELECT nombre FROM modulos WHERE activo = 1 ORDER BY CHAR_LENGTH(nombre) DESC");
        $modulos = $stmtMod ? $stmtMod->fetchAll(PDO::FETCH_COLUMN) : [];

        $modulo_nombre = null;
        $accion_lower = strtolower(trim($accion));

        foreach ($modulos as $mod) {
            $mod_lower = strtolower($mod);
            // La acción coincide exactamente con el módulo o comienza con 'modulo_'
            if ($accion_lower === $mod_lower || str_starts_with($accion_lower, $mod_lower . '_')) {
                $modulo_nombre = $mod;
                break;
            }
        }

        // Si no coincidió con ningún módulo activo de la base de datos, fallback al prefijo simple
        if (!$modulo_nombre) {
            $modulo_nombre = explode('_', $accion)[0];
        }

        // Verificar en base de datos si el rol tiene asignado ese módulo
        $query = "
            SELECT p.id 
            FROM permisos_tipousuario p 
            JOIN modulos m ON p.modulo_id = m.id 
            WHERE LOWER(p.tipousuario) = LOWER(:tipousuario) 
            AND LOWER(m.nombre) = LOWER(:modulo_nombre)
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipousuario', $tipousuario);
        $stmt->bindParam(':modulo_nombre', $modulo_nombre);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
?>
