<?php

class Postulado {
    private $conn;
    private $table_name = "postulados";

    public $tipomiembro;
    public $cedula;
    public $primer_nombre;
    public $segundo_nombre;
    public $primer_apellido;
    public $segundo_apellido;
    public $nombre; // For compatibility/display
    public $correo_electronico;
    public $profesion;
    public $tipo_referencia;
    public $referencia_id;
    public $foto;
    public $nacionalidad_id;
    public $nac_codigo;

    public $en_votacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($filters = [], $limit = null, $offset = null) {
        if (is_string($filters)) {
            $filters = ['keyword' => $filters];
        }

        $query = "SELECT 
                    p.tipomiembro, p.cedula, p.nacionalidad_id,
                    n.codigo AS nac_codigo,
                    p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido,
                    CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, '')) as nombre,
                    p.profesion, p.correo_electronico, p.tipo_referencia, p.referencia_id, p.foto, p.en_votacion
                  FROM " . $this->table_name . " p
                  LEFT JOIN nacionalidades n ON p.nacionalidad_id = n.id
                  WHERE 1=1";
        
        $params = [];

        // Filtro Nombre (Multi-palabra)
        if (!empty($filters['nombre'])) {
            $palabras = explode(' ', trim($filters['nombre']));
            $concatSql = "CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, ''))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $query .= " AND $concatSql LIKE ?";
                $params[] = "%" . trim($palabra) . "%";
            }
        }

        // Filtro Cédula
        if (!empty($filters['cedula'])) {
            $query .= " AND p.cedula LIKE ?";
            $params[] = "%" . trim($filters['cedula']) . "%";
        }

        // Keyword Global
        if (!empty($filters['keyword'])) {
            $palabras = explode(' ', trim($filters['keyword']));
            $concatSql = "CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, ''))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $p = "%" . trim($palabra) . "%";
                $query .= " AND (p.cedula LIKE ? OR $concatSql LIKE ? OR p.profesion LIKE ?)";
                $params[] = $p; $params[] = $p; $params[] = $p;
            }
        }

        $query .= " ORDER BY p.tipomiembro, p.primer_apellido, p.primer_nombre ASC";
                  
        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT ?, ?";
            $params[] = (int)$offset;
            $params[] = (int)$limit;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $index => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($index + 1, $value, $type);
        }

        $stmt->execute();
        return $stmt;
    }

    public function count($filters = []) {
        if (is_string($filters)) {
            $filters = ['keyword' => $filters];
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";
        $params = [];

        // Filtro Nombre (Multi-palabra)
        if (!empty($filters['nombre'])) {
            $palabras = explode(' ', trim($filters['nombre']));
            $concatSql = "CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, ''))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $query .= " AND $concatSql LIKE ?";
                $params[] = "%" . trim($palabra) . "%";
            }
        }

        // Filtro Cédula
        if (!empty($filters['cedula'])) {
            $query .= " AND p.cedula LIKE ?";
            $params[] = "%" . trim($filters['cedula']) . "%";
        }

        // Keyword Global
        if (!empty($filters['keyword'])) {
            $palabras = explode(' ', trim($filters['keyword']));
            $concatSql = "CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, ''))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $p = "%" . trim($palabra) . "%";
                $query .= " AND (p.cedula LIKE ? OR $concatSql LIKE ? OR p.profesion LIKE ?)";
                $params[] = $p; $params[] = $p; $params[] = $p;
            }
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function readOne() {
        $query = "SELECT p.*, n.codigo AS nac_codigo 
                  FROM " . $this->table_name . " p
                  LEFT JOIN nacionalidades n ON p.nacionalidad_id = n.id
                  WHERE p.cedula = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->cedula);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->tipomiembro = $row['tipomiembro'];
            $this->nombre = trim($row['primer_nombre'] . ' ' . $row['segundo_nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido']);
            $this->primer_nombre = $row['primer_nombre'];
            $this->segundo_nombre = $row['segundo_nombre'];
            $this->primer_apellido = $row['primer_apellido'];
            $this->segundo_apellido = $row['segundo_apellido'];
            $this->correo_electronico = $row['correo_electronico'];
            $this->profesion = $row['profesion'];
            $this->tipo_referencia = $row['tipo_referencia'];
            $this->referencia_id = $row['referencia_id'];
            $this->foto = $row['foto'];
            $this->nacionalidad_id = $row['nacionalidad_id'];
            $this->nac_codigo = $row['nac_codigo'];
            return true;
        }
        return false;
    }

    public function create() {
        // Concatenar nombre para objeto (no se guarda en BD)
        $this->nombre = trim($this->primer_nombre . ' ' . $this->segundo_nombre . ' ' . $this->primer_apellido . ' ' . $this->segundo_apellido);

        $query = "INSERT INTO " . $this->table_name . " 
                  SET tipomiembro=:tipomiembro, cedula=:cedula,
                      primer_nombre=:pn, segundo_nombre=:sn, primer_apellido=:pa, segundo_apellido=:sa,
                      correo_electronico=:correo_electronico, profesion=:profesion, 
                      tipo_referencia=:tipo_referencia, referencia_id=:referencia_id, nacionalidad_id=:nacionalidad_id, foto=:foto";
        $stmt = $this->conn->prepare($query);

        $this->tipomiembro = htmlspecialchars(strip_tags($this->tipomiembro));
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $this->primer_nombre = htmlspecialchars(strip_tags($this->primer_nombre));
        $this->segundo_nombre = htmlspecialchars(strip_tags($this->segundo_nombre));
        $this->primer_apellido = htmlspecialchars(strip_tags($this->primer_apellido));
        $this->segundo_apellido = htmlspecialchars(strip_tags($this->segundo_apellido));

        $this->correo_electronico = htmlspecialchars(strip_tags($this->correo_electronico));
        $this->profesion = htmlspecialchars(strip_tags($this->profesion));
        $this->tipo_referencia = htmlspecialchars(strip_tags($this->tipo_referencia));
        $this->referencia_id = htmlspecialchars(strip_tags($this->referencia_id));
        $this->nacionalidad_id = (int) htmlspecialchars(strip_tags($this->nacionalidad_id));

        $stmt->bindParam(":tipomiembro", $this->tipomiembro);
        $stmt->bindParam(":cedula", $this->cedula);
        $stmt->bindParam(":pn", $this->primer_nombre);
        $stmt->bindParam(":sn", $this->segundo_nombre);
        $stmt->bindParam(":pa", $this->primer_apellido);
        $stmt->bindParam(":sa", $this->segundo_apellido);

        $stmt->bindParam(":correo_electronico", $this->correo_electronico);
        $stmt->bindParam(":profesion", $this->profesion);
        $stmt->bindParam(":tipo_referencia", $this->tipo_referencia);
        $stmt->bindParam(":referencia_id", $this->referencia_id);
        $stmt->bindParam(":nacionalidad_id", $this->nacionalidad_id);
        $stmt->bindParam(":foto", $this->foto);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        // Concatenar nombre
        $this->nombre = trim($this->primer_nombre . ' ' . $this->segundo_nombre . ' ' . $this->primer_apellido . ' ' . $this->segundo_apellido);

        $query = "UPDATE " . $this->table_name . " 
                  SET tipomiembro=:tipomiembro,
                      primer_nombre=:pn, segundo_nombre=:sn, primer_apellido=:pa, segundo_apellido=:sa,
                      correo_electronico=:correo_electronico, profesion=:profesion,
                      tipo_referencia=:tipo_referencia, referencia_id=:referencia_id, nacionalidad_id=:nacionalidad_id, foto=:foto
                  WHERE cedula=:cedula";
        $stmt = $this->conn->prepare($query);

        $this->tipomiembro = htmlspecialchars(strip_tags($this->tipomiembro));
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $this->primer_nombre = htmlspecialchars(strip_tags($this->primer_nombre));
        $this->segundo_nombre = htmlspecialchars(strip_tags($this->segundo_nombre));
        $this->primer_apellido = htmlspecialchars(strip_tags($this->primer_apellido));
        $this->segundo_apellido = htmlspecialchars(strip_tags($this->segundo_apellido));

        $this->correo_electronico = htmlspecialchars(strip_tags($this->correo_electronico));
        $this->profesion = htmlspecialchars(strip_tags($this->profesion));
        $this->tipo_referencia = htmlspecialchars(strip_tags($this->tipo_referencia));
        $this->referencia_id = htmlspecialchars(strip_tags($this->referencia_id));
        $this->nacionalidad_id = (int) htmlspecialchars(strip_tags($this->nacionalidad_id));

        $stmt->bindParam(":tipomiembro", $this->tipomiembro);
        $stmt->bindParam(":cedula", $this->cedula);
        $stmt->bindParam(":pn", $this->primer_nombre);
        $stmt->bindParam(":sn", $this->segundo_nombre);
        $stmt->bindParam(":pa", $this->primer_apellido);
        $stmt->bindParam(":sa", $this->segundo_apellido);

        $stmt->bindParam(":correo_electronico", $this->correo_electronico);
        $stmt->bindParam(":profesion", $this->profesion);
        $stmt->bindParam(":tipo_referencia", $this->tipo_referencia);
        $stmt->bindParam(":referencia_id", $this->referencia_id);
        $stmt->bindParam(":nacionalidad_id", $this->nacionalidad_id);
        $stmt->bindParam(":foto", $this->foto);


        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE cedula = ?";
        $stmt = $this->conn->prepare($query);
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $stmt->bindParam(1, $this->cedula);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByTipo($tipo) {
        $query = "SELECT *, CONCAT_WS(' ', NULLIF(primer_nombre, ''), NULLIF(segundo_nombre, ''), NULLIF(primer_apellido, ''), NULLIF(segundo_apellido, '')) as nombre 
                  FROM " . $this->table_name . " 
                  WHERE tipomiembro = ? 
                  ORDER BY primer_apellido, primer_nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tipo);
        $stmt->execute();
        return $stmt;
    }

    public function getSociosReferencia() {
        // Obtenemos id, nombre y numero_accion
        $query = "SELECT p.id, p.primer_nombre, p.primer_apellido, a.numero_accion,
                  CONCAT(p.primer_nombre, ' ', p.primer_apellido, ' (Acción: ', a.numero_accion, ')') as display_text
                  FROM personas p
                  JOIN acciones a ON p.id = a.persona_id
                  WHERE a.estado = 'ACTIVO'
                  ORDER BY p.primer_nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getEmpresasReferencia() {
        // Obtenemos id_empresa, nombre_empresa, rif_empresa
        $query = "SELECT id_empresa as id, nombre_empresa, rif_empresa,
                  CONCAT(nombre_empresa, ' (RIF: ', rif_empresa, ')') as display_text
                  FROM empresas
                  ORDER BY nombre_empresa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getProfesiones() {
        $query = "SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Actualizar estado de participación en votación
    public function updateEnVotacion($cedulas = []) {
        // Primero desactivar todos
        $this->conn->exec("UPDATE " . $this->table_name . " SET en_votacion = 0");
        
        // Si hay cédulas seleccionadas, activarlas
        if (!empty($cedulas)) {
            $placeholders = str_repeat('?,', count($cedulas) - 1) . '?';
            $query = "UPDATE " . $this->table_name . " SET en_votacion = 1 WHERE cedula IN ($placeholders)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($cedulas);
        }
        
        return true;
    }

    // Obtener postulados activos para votación
    public function readEnVotacion() {
        $query = "SELECT 
                    tipomiembro, cedula, nacionalidad_id,
                    primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
                    CONCAT_WS(' ', NULLIF(primer_nombre, ''), NULLIF(segundo_nombre, ''), NULLIF(primer_apellido, ''), NULLIF(segundo_apellido, '')) as nombre,
                    profesion, correo_electronico, foto, en_votacion
                  FROM " . $this->table_name . " 
                  WHERE en_votacion = 1
                  ORDER BY tipomiembro, primer_apellido, primer_nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getNacionalidades() {
        $query = "SELECT id, codigo, nombre FROM nacionalidades WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
