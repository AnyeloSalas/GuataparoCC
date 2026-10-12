<?php

class Conyugue {
    private $conn;
    // Usamos la vista para lecturas
    private $view_name = "v_acciones_completas";
    // Tablas base para escrituras
    private $table_acciones = "acciones";
    private $table_conyugue = "conyugue";

    public $accionconyugue; // numero_accion
    public $cedulaconyugue; // conyuge_cedula
    public $nacionalidad_id;
    public $nacionalidad_codigo; // código de nacionalidad (V, E, P)
    public $primer_nombre;
    public $segundo_nombre;
    public $primer_apellido;
    public $segundo_apellido;
    public $tipo_relacion;   // Relación (Hijo, Cónyuge, etc.)
    public $estado_civil;    // Estado civil
    
    // Propiedad legacy
    public $nombreconyugue; // conyuge_nombre

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($keyword = null, $limit = null, $offset = null) {
        // Query que obtiene los familiares desde la tabla dedicada conyugue
        $query = "SELECT 
                    a.numero_accion as accionconyugue, 
                    c.cedula as cedulaconyugue, 
                    c.nacionalidad_id,
                    COALESCE(n.codigo, 'V') as nacionalidad_codigo,
                    CONCAT_WS(' ', c.primer_nombre, c.segundo_nombre, c.primer_apellido, c.segundo_apellido) as nombreconyugue,
                    c.primer_nombre,
                    c.segundo_nombre,
                    c.primer_apellido,
                    c.segundo_apellido, 
                    c.tipo_relacion, 
                    c.estado_civil 
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_conyugue . " c ON a.id = c.accion_id
                  LEFT JOIN nacionalidades n ON c.nacionalidad_id = n.id";

        if ($keyword) {
            $query .= " WHERE (a.numero_accion LIKE :k1 
                        OR c.cedula LIKE :k2 
                        OR c.primer_nombre LIKE :k3
                        OR c.primer_apellido LIKE :k4)";
        }
        
        $query .= " ORDER BY a.numero_accion ASC";
        
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($keyword) {
            $keyword = "%{$keyword}%";
            $stmt->bindValue(':k1', $keyword);
            $stmt->bindValue(':k2', $keyword);
            $stmt->bindValue(':k3', $keyword);
            $stmt->bindValue(':k4', $keyword);
        }

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function count($keyword = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_conyugue . " c ON a.id = c.accion_id";

        if ($keyword) {
            $query .= " WHERE (a.numero_accion LIKE :k1 
                        OR c.cedula LIKE :k2 
                        OR c.primer_nombre LIKE :k3
                        OR c.primer_apellido LIKE :k4)";
        }

        $stmt = $this->conn->prepare($query);

        if ($keyword) {
            $keyword = "%{$keyword}%";
            $stmt->bindValue(':k1', $keyword);
            $stmt->bindValue(':k2', $keyword);
            $stmt->bindValue(':k3', $keyword);
            $stmt->bindValue(':k4', $keyword);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function readOne() {
        $query = "SELECT 
                    a.numero_accion as accionconyugue, 
                    c.cedula as cedulaconyugue, 
                    c.nacionalidad_id,
                    COALESCE(n.codigo, 'V') as nacionalidad_codigo,
                    CONCAT_WS(' ', c.primer_nombre, c.segundo_nombre, c.primer_apellido, c.segundo_apellido) as nombreconyugue,
                    c.primer_nombre,
                    c.segundo_nombre,
                    c.primer_apellido,
                    c.segundo_apellido,
                    c.tipo_relacion, 
                    c.estado_civil 
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_conyugue . " c ON a.id = c.accion_id
                  LEFT JOIN nacionalidades n ON c.nacionalidad_id = n.id
                  WHERE a.numero_accion = ? LIMIT 0,1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->accionconyugue);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->cedulaconyugue = $row['cedulaconyugue'];
            $this->nacionalidad_id = $row['nacionalidad_id'];
            $this->nacionalidad_codigo = $row['nacionalidad_codigo'];
            $this->nombreconyugue = $row['nombreconyugue'];
            $this->primer_nombre = $row['primer_nombre'];
            $this->segundo_nombre = $row['segundo_nombre'];
            $this->primer_apellido = $row['primer_apellido'];
            $this->segundo_apellido = $row['segundo_apellido'];
            $this->tipo_relacion = $row['tipo_relacion'];
            $this->estado_civil = $row['estado_civil'];
            return true;
        }
        return false;
    }

    public function existsByCedula($cedula, $excludeAccion = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_conyugue . " WHERE cedula = ?";
        if ($excludeAccion !== null) {
            $query .= " AND accion_id != (SELECT id FROM acciones WHERE numero_accion = ?)";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cedula);
        if ($excludeAccion !== null) {
            $stmt->bindParam(2, $excludeAccion);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener ID de la acción
            $query_acc = "SELECT id FROM " . $this->table_acciones . " WHERE numero_accion = :acc LIMIT 1";
            $stmt_acc = $this->conn->prepare($query_acc);
            $stmt_acc->execute([':acc' => $this->accionconyugue]);
            $accion_id = $stmt_acc->fetchColumn();

            if (!$accion_id) {
                throw new Exception("Acción no encontrada.");
            }

            // 2. Insertar en tabla conyugue
            $query_in = "INSERT INTO " . $this->table_conyugue . " 
                        (accion_id, cedula, nacionalidad_id, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, estado_civil, tipo_relacion) 
                        VALUES (:acc_id, :cedula, :nac_id, :pn, :sn, :pa, :sa, :ec, :tr)
                        ON DUPLICATE KEY UPDATE 
                        cedula = VALUES(cedula),
                        nacionalidad_id = VALUES(nacionalidad_id),
                        primer_nombre = VALUES(primer_nombre),
                        segundo_nombre = VALUES(segundo_nombre),
                        primer_apellido = VALUES(primer_apellido),
                        segundo_apellido = VALUES(segundo_apellido),
                        estado_civil = VALUES(estado_civil),
                        tipo_relacion = VALUES(tipo_relacion)";
            
            $stmt_in = $this->conn->prepare($query_in);
            $stmt_in->bindParam(":acc_id", $accion_id);
            $stmt_in->bindParam(":cedula", $this->cedulaconyugue);
            $stmt_in->bindParam(":nac_id", $this->nacionalidad_id);
            $stmt_in->bindParam(":pn", $this->primer_nombre);
            $stmt_in->bindParam(":sn", $this->segundo_nombre);
            $stmt_in->bindParam(":pa", $this->primer_apellido);
            $stmt_in->bindParam(":sa", $this->segundo_apellido);
            $stmt_in->bindParam(":ec", $this->estado_civil);
            $stmt_in->bindParam(":tr", $this->tipo_relacion);
            $stmt_in->execute();
            
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function update() {
        return $this->create(); // Usamos ON DUPLICATE KEY UPDATE en create
    }

    public function delete() {
        // Eliminar de la tabla dedicada
        $query = "DELETE c FROM " . $this->table_conyugue . " c
                  INNER JOIN " . $this->table_acciones . " a ON c.accion_id = a.id
                  WHERE a.numero_accion = ?";
                  
        $stmt = $this->conn->prepare($query);
        $this->accionconyugue = htmlspecialchars(strip_tags($this->accionconyugue));
        $stmt->bindParam(1, $this->accionconyugue);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($keyword) {
        return $this->read($keyword);
    }

    public function getByAccion($accion) {
        $this->accionconyugue = $accion;
        $stmt = $this->conn->prepare("SELECT 
                    a.numero_accion as accionconyugue, 
                    c.cedula as cedulaconyugue, 
                    c.nacionalidad_id,
                    COALESCE(n.codigo, 'V') as nacionalidad_codigo,
                    CONCAT_WS(' ', c.primer_nombre, c.segundo_nombre, c.primer_apellido, c.segundo_apellido) as nombreconyugue, 
                    c.tipo_relacion, 
                    c.estado_civil 
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_conyugue . " c ON a.id = c.accion_id
                  LEFT JOIN nacionalidades n ON c.nacionalidad_id = n.id
                  WHERE a.numero_accion = ?");
        $stmt->bindParam(1, $accion);
        $stmt->execute();
        return $stmt;
    }
}
?>
