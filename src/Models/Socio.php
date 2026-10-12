<?php

class Socio {
    private $conn;
    // Usamos la vista para lecturas
    private $view_name = "v_acciones_completas";
    // Tablas base para escrituras
    private $table_acciones = "acciones";
    private $table_personas = "personas";

    public $accion; // numero_accion
    public $cedula; // titular_cedula
    public $primer_nombre;
    public $segundo_nombre;
    public $primer_apellido;
    public $segundo_apellido;
    public $estado;
    public $cedula_conyu;
    public $usuario_refe;
    
    // Propiedades heredadas para compatibilidad
    public $nombre; // nombre_completo

    // Nuevos campos para normalización
    public $nacionalidad;
    public $email;
    public $telefono_movil;
    public $sexo;
    public $fecha_nacimiento;
    public $profesion_id;
    public $lugar_trabajo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        // Usamos la vista normalizada
        $query = "SELECT 
                    numero_accion as accion, 
                    titular_nacionalidad as nacionalidad_codigo,
                    titular_cedula as cedula, 
                    titular_nombre as nombre, 
                    estado 
                  FROM " . $this->view_name . " 
                  ORDER BY numero_accion ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readWithFingerprints($filters = [], $offset = null, $limit = null) {
        // Manejar búsqueda legacy (string) o filtros específicos (array)
        if (is_string($filters)) {
            $filters = ['keyword' => $filters];
        }

        $query = "SELECT 
                    a.numero_accion as accion, 
                    p.cedula, 
                    TRIM(CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, ''))) as nombre, 
                    p.primer_nombre,
                    p.segundo_nombre,
                    p.primer_apellido,
                    p.segundo_apellido,
                    p.email,
                    a.estado,
                    COALESCE(ti.codigo, 'V') as nacionalidad_codigo,
                    CASE WHEN p.huella_template IS NOT NULL AND p.huella_template != '' THEN 1 ELSE 0 END as tiene_huella
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_personas . " p ON a.persona_id = p.id
                  LEFT JOIN tipos_identificacion ti ON p.tipo_identificacion_id = ti.id
                  WHERE 1=1";

        $params = [];

        // Búsqueda General (Legacy)
        if (!empty($filters['keyword'])) {
            $palabras = explode(' ', trim($filters['keyword']));
            $concatSql = "TRIM(CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, '')))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $p = "%" . trim($palabra) . "%";
                $query .= " AND (a.numero_accion LIKE ? OR p.cedula LIKE ? OR $concatSql LIKE ?)";
                $params[] = $p; $params[] = $p; $params[] = $p;
            }
        }

        // Filtros Específicos
        if (!empty($filters['nombre'])) {
            $nombreBusqueda = trim($filters['nombre']);
            $palabras = explode(' ', $nombreBusqueda);
            $concatSql = "TRIM(CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, '')))";
            
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $query .= " AND $concatSql LIKE ?";
                $params[] = "%" . trim($palabra) . "%";
            }
        }

        if (!empty($filters['cedula'])) {
            $query .= " AND p.cedula LIKE ?";
            $params[] = "%{$filters['cedula']}%";
        }

        if (!empty($filters['accion'])) {
            $query .= " AND a.numero_accion = ?";
            $params[] = $filters['accion'];
        }
        
        $query .= " ORDER BY a.numero_accion ASC";

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Vincular parámetros
        foreach ($params as $index => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($index + 1, $value, $type);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function existsByCedulaInAcciones($cedula, $excludeAccion = null) {
        $query = "SELECT a.numero_accion 
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_personas . " p ON a.persona_id = p.id
                  WHERE p.cedula = :cedula";
        
        if ($excludeAccion !== null) {
            $query .= " AND a.numero_accion != :exclude";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cedula", $cedula);
        if ($excludeAccion !== null) {
            $stmt->bindParam(":exclude", $excludeAccion);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function countWithFingerprints($filters = []) {
        // Manejar búsqueda legacy (string) o filtros específicos (array)
        if (is_string($filters)) {
            $filters = ['keyword' => $filters];
        }

        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_personas . " p ON a.persona_id = p.id
                  WHERE 1=1";

        $params = [];

        // Búsqueda General (Legacy)
        if (!empty($filters['keyword'])) {
            $palabras = explode(' ', trim($filters['keyword']));
            $concatSql = "TRIM(CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, '')))";
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $p = "%" . trim($palabra) . "%";
                $query .= " AND (a.numero_accion LIKE ? OR p.cedula LIKE ? OR $concatSql LIKE ?)";
                $params[] = $p; $params[] = $p; $params[] = $p;
            }
        }

        // Filtros Específicos
        if (!empty($filters['nombre'])) {
            $nombreBusqueda = trim($filters['nombre']);
            $palabras = explode(' ', $nombreBusqueda);
            $concatSql = "TRIM(CONCAT_WS(' ', NULLIF(p.primer_nombre, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.primer_apellido, ''), NULLIF(p.segundo_apellido, '')))";
            
            foreach ($palabras as $palabra) {
                if (empty(trim($palabra))) continue;
                $query .= " AND $concatSql LIKE ?";
                $params[] = "%" . trim($palabra) . "%";
            }
        }

        if (!empty($filters['cedula'])) {
            $query .= " AND p.cedula LIKE ?";
            $params[] = "%{$filters['cedula']}%";
        }

        if (!empty($filters['accion'])) {
            $query .= " AND a.numero_accion = ?";
            $params[] = $filters['accion'];
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function create() {
        try {
            $this->conn->beginTransaction();

            // 1. Gestionar Persona (Titular)
            // Verificar si la persona ya existe
            $query_check = "SELECT id FROM " . $this->table_personas . " WHERE cedula = :cedula LIMIT 1";
            $stmt_check = $this->conn->prepare($query_check);
            
            $this->cedula = htmlspecialchars(strip_tags($this->cedula));
            
            $stmt_check->bindParam(":cedula", $this->cedula);
            $stmt_check->execute();
            
            $persona_id = null;
            
            if ($stmt_check->rowCount() > 0) {
                // Persona existe, obtenemos ID
                $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $persona_id = $row['id'];
                
                // Actualizar nuevos campos
                $query_update_p = "UPDATE " . $this->table_personas . " 
                                  SET primer_nombre = :pn, 
                                      segundo_nombre = :sn, 
                                      primer_apellido = :pa, 
                                      segundo_apellido = :sa,
                                      email = :email,
                                      telefono_movil = :tlf,
                                      sexo = :sexo,
                                      fecha_nacimiento = :fnac,
                                      profesion_id = :prof_id,
                                      lugar_trabajo = :ltra
                                  WHERE id = :id";
                $stmt_up = $this->conn->prepare($query_update_p);
                $stmt_up->bindParam(":pn", $this->primer_nombre);
                $stmt_up->bindParam(":sn", $this->segundo_nombre);
                $stmt_up->bindParam(":pa", $this->primer_apellido);
                $stmt_up->bindParam(":sa", $this->segundo_apellido);
                $stmt_up->bindParam(":email", $this->email);
                $stmt_up->bindParam(":tlf", $this->telefono_movil);
                $stmt_up->bindParam(":sexo", $this->sexo);
                $stmt_up->bindParam(":fnac", $this->fecha_nacimiento);
                $stmt_up->bindParam(":prof_id", $this->profesion_id);
                $stmt_up->bindParam(":ltra", $this->lugar_trabajo);
                $stmt_up->bindParam(":id", $persona_id);
                $stmt_up->execute();
                
            } else {
                // Obtener tipo_identificacion_id basado en el código de la cédula/nacionalidad (V, E, P)
                $tipo_id = 1; // Default Venezolano
                $q_tipo = "SELECT id FROM tipos_identificacion WHERE codigo = :cod LIMIT 1";
                $st_tipo = $this->conn->prepare($q_tipo);
                $st_tipo->execute([':cod' => $this->nacionalidad ?: 'V']);
                if($r_tipo = $st_tipo->fetch()) $tipo_id = $r_tipo['id'];

                // Persona no existe, crearla
                $query_insert_p = "INSERT INTO " . $this->table_personas . " 
                                  (cedula, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, 
                                   email, telefono_movil, sexo, fecha_nacimiento, profesion_id, lugar_trabajo, tipo_identificacion_id) 
                                  VALUES (:cedula, :pn, :sn, :pa, :sa, 
                                   :email, :tlf, :sexo, :fnac, :prof_id, :ltra, :tipo_id)";
                $stmt_in_p = $this->conn->prepare($query_insert_p);
                $stmt_in_p->bindParam(":cedula", $this->cedula);
                $stmt_in_p->bindParam(":pn", $this->primer_nombre);
                $stmt_in_p->bindParam(":sn", $this->segundo_nombre);
                $stmt_in_p->bindParam(":pa", $this->primer_apellido);
                $stmt_in_p->bindParam(":sa", $this->segundo_apellido);
                $stmt_in_p->bindParam(":email", $this->email);
                $stmt_in_p->bindParam(":tlf", $this->telefono_movil);
                $stmt_in_p->bindParam(":sexo", $this->sexo);
                $stmt_in_p->bindParam(":fnac", $this->fecha_nacimiento);
                $stmt_in_p->bindParam(":prof_id", $this->profesion_id);
                $stmt_in_p->bindParam(":ltra", $this->lugar_trabajo);
                $stmt_in_p->bindParam(":tipo_id", $tipo_id);
                $stmt_in_p->execute();
                $persona_id = $this->conn->lastInsertId();
            }

            // 2. Crear Acción
            // Obtener el próximo ID manual para acciones de forma atómica y bloqueada (FOR UPDATE)
            $stmt_id_acc = $this->conn->query("SELECT COALESCE(MAX(id), 0) as max FROM " . $this->table_acciones . " FOR UPDATE");
            $next_id_acc = ($stmt_id_acc->fetch(PDO::FETCH_ASSOC)['max'] ?? 0) + 1;

            $query_accion = "INSERT INTO " . $this->table_acciones . " 
                            (id, numero_accion, persona_id, estado, usuario_username) 
                            VALUES (:id, :accion, :persona_id, :estado, :usuario_refe)";
            
            $stmt_acc = $this->conn->prepare($query_accion);
            
            $this->accion = htmlspecialchars(strip_tags($this->accion));
            $this->estado = htmlspecialchars(strip_tags($this->estado));
            
            // Default usuario_refe
            if (!isset($this->usuario_refe) || empty($this->usuario_refe)) {
                $this->usuario_refe = 'SYSTEM';
            }
            $this->usuario_refe = htmlspecialchars(strip_tags($this->usuario_refe));

            $stmt_acc->bindParam(":id", $next_id_acc);
            $stmt_acc->bindParam(":accion", $this->accion);
            $stmt_acc->bindParam(":persona_id", $persona_id);
            $stmt_acc->bindParam(":estado", $this->estado);
            $stmt_acc->bindParam(":usuario_refe", $this->usuario_refe);
            
            if ($stmt_acc->execute()) {
                // Manejar cónyuge si existe
                if (isset($this->cedula_conyu) && !empty($this->cedula_conyu) && $this->cedula_conyu != '0') {
                    // Pendiente
                }
                
                $this->conn->commit();
                return true;
            }
            
            $this->conn->rollBack();
            return false;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function readOne() {
        $query = "SELECT 
                    a.numero_accion as accion, 
                    p.cedula, 
                    TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido)) as nombre, 
                    p.primer_nombre,
                    p.segundo_nombre,
                    p.primer_apellido,
                    p.segundo_apellido,
                    p.email,
                    a.estado,
                    COALESCE(ti.codigo, 'V') as nacionalidad_codigo
                  FROM " . $this->table_acciones . " a
                  INNER JOIN " . $this->table_personas . " p ON a.persona_id = p.id
                  LEFT JOIN tipos_identificacion ti ON p.tipo_identificacion_id = ti.id
                  WHERE a.numero_accion = ? LIMIT 0,1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->accion);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->cedula = $row['cedula'];
            $this->nombre = $row['nombre'];
            $this->primer_nombre = $row['primer_nombre'];
            $this->segundo_nombre = $row['segundo_nombre'];
            $this->primer_apellido = $row['primer_apellido'];
            $this->segundo_apellido = $row['segundo_apellido'];
            $this->email = $row['email'];
            $this->estado = $row['estado'];
            $this->nacionalidad = $row['nacionalidad_codigo'];
            return true;
        }
        return false;
    }

    public function update() {
        try {
            $this->conn->beginTransaction();
            
            // 1. Obtener ID de persona asociada a la acción
            $query_get = "SELECT persona_id FROM " . $this->table_acciones . " WHERE numero_accion = :accion";
            $stmt_get = $this->conn->prepare($query_get);
            $stmt_get->bindParam(":accion", $this->accion);
            $stmt_get->execute();
            
            if ($stmt_get->rowCount() == 0) {
                return false;
            }
            
            $row = $stmt_get->fetch(PDO::FETCH_ASSOC);
            $persona_id = $row['persona_id'];
            
            // 2. Actualizar Persona
            $query_p = "UPDATE " . $this->table_personas . " 
                       SET cedula = :cedula, 
                           primer_nombre = :pn,
                           segundo_nombre = :sn,
                           primer_apellido = :pa,
                           segundo_apellido = :sa,
                           email = :email
                       WHERE id = :id";
            
            $stmt_p = $this->conn->prepare($query_p);
            
            $this->cedula = htmlspecialchars(strip_tags($this->cedula));
            $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
            
            $stmt_p->bindParam(":cedula", $this->cedula);
            $stmt_p->bindParam(":pn", $this->primer_nombre);
            $stmt_p->bindParam(":sn", $this->segundo_nombre);
            $stmt_p->bindParam(":pa", $this->primer_apellido);
            $stmt_p->bindParam(":sa", $this->segundo_apellido);
            $stmt_p->bindParam(":email", $this->email);
            $stmt_p->bindParam(":id", $persona_id);
            $stmt_p->execute();
            
            // 3. Actualizar Acción
            $query_a = "UPDATE " . $this->table_acciones . " SET estado = :estado WHERE numero_accion = :accion";
            $stmt_a = $this->conn->prepare($query_a);
            
            $this->estado = htmlspecialchars(strip_tags($this->estado));
            $this->accion = htmlspecialchars(strip_tags($this->accion));
            
            $stmt_a->bindParam(":estado", $this->estado);
            $stmt_a->bindParam(":accion", $this->accion);
            
            if ($stmt_a->execute()) {
                $this->conn->commit();
                return true;
            }
            
            $this->conn->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete() {
        // Solo eliminamos la acción, mantenemos la persona en el sistema
        $query = "DELETE FROM " . $this->table_acciones . " WHERE numero_accion = ?";
        $stmt = $this->conn->prepare($query);
        $this->accion = htmlspecialchars(strip_tags($this->accion));
        $stmt->bindParam(1, $this->accion);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($keyword) {
        $query = "SELECT 
                    numero_accion as accion, 
                    titular_nacionalidad as nacionalidad_codigo,
                    titular_cedula as cedula, 
                    titular_nombre as nombre, 
                    estado 
                  FROM " . $this->view_name . " 
                  WHERE titular_nombre LIKE ? OR titular_cedula LIKE ? 
                  ORDER BY numero_accion ASC";
                  
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_acciones;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Obtener acciones activas para votación
    public function getAccionesActivas() {
        $query = "SELECT 
                    a.numero_accion as accion, 
                    p.cedula, 
                    TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido)) as nombre,
                    CONCAT(a.numero_accion, ' - ', TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido))) as display_text
                  FROM acciones a
                  JOIN personas p ON a.persona_id = p.id
                  WHERE a.estado = 'ACTIVO'
                  ORDER BY a.numero_accion ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccionesPorSocio($cedula) {
        $query = "SELECT 
                    a.numero_accion as accion, 
                    p.cedula, 
                    TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido)) as nombre,
                    CONCAT(a.numero_accion, ' - ', TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido))) as display_text
                  FROM acciones a
                  JOIN personas p ON a.persona_id = p.id
                  WHERE a.estado = 'ACTIVO' AND p.cedula = :cedula
                  ORDER BY a.numero_accion ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setStatus($status, $motivo = null) {
        try {
            $this->conn->beginTransaction();
            
            $status = strtoupper(htmlspecialchars(strip_tags($status)));
            $this->accion = (int)htmlspecialchars(strip_tags($this->accion));
            $motivo = $motivo ? htmlspecialchars(strip_tags($motivo)) : 'Cambio de estado desde sistema';
            
            // Verificar existencia
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM acciones WHERE numero_accion = ?");
            $stmt->execute([$this->accion]);
            if ($stmt->fetchColumn() == 0) {
                $this->conn->rollBack();
                return false;
            }
            
            if (!in_array($status, ['ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'TRANSFERIDO', 'CANCELADO'])) {
                $this->conn->rollBack();
                return false;
            }

            if ($status === 'ACTIVO') {
                $query = "UPDATE acciones 
                          SET estado = ?, 
                              fecha_activacion = CURRENT_DATE,
                              motivo_inactivacion = NULL,
                              updated_at = CURRENT_TIMESTAMP
                          WHERE numero_accion = ?";
                $stmt_upd = $this->conn->prepare($query);
                $stmt_upd->execute([$status, $this->accion]);
            } else {
                $query = "UPDATE acciones 
                          SET estado = ?, 
                              fecha_inactivacion = CURRENT_DATE,
                              motivo_inactivacion = ?,
                              updated_at = CURRENT_TIMESTAMP
                          WHERE numero_accion = ?";
                $stmt_upd = $this->conn->prepare($query);
                $stmt_upd->execute([$status, $motivo, $this->accion]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
