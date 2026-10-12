<?php

class Reporte
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ==================== ESTADÍSTICAS GENERALES ====================

    /**
     * Obtiene estadísticas generales del sistema (totales de socios, cónyuges, usuarios, huellas, etc.).
     * 
     * @return array Arreglo asociativo con las estadísticas generales.
     */
    public function getEstadisticasGenerales()
    {
        $stats = [];

        // Total de socios (acciones)
        $query = "SELECT COUNT(*) as total FROM acciones";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_socios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Socios por estado
        $query = "SELECT estado, COUNT(*) as total FROM acciones GROUP BY estado";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['socios_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total de cónyuges registrados (tabla dedicada)
        $query = "SELECT COUNT(*) as total FROM conyugue";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_conyugues'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de usuarios
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de huellas registradas (en personas)
        $query = "SELECT COUNT(*) as total FROM personas WHERE huella_template IS NOT NULL AND huella_template != ''";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_huellas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de votaciones registradas
        $query = "SELECT COUNT(*) as total FROM votacion";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_votaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de postulados registrados
        $query = "SELECT COUNT(*) as total FROM postulados";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_postulados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    // ==================== REPORTES DE SOCIOS ====================

    /**
     * Obtiene el reporte detallado de socios basado en los filtros proporcionados.
     * 
     * @param array $filtros Arreglo asociativo con los filtros a aplicar (estado, sexo, profesion, etc.).
     * @param int $offset Desplazamiento para la paginación.
     * @param int $limit Límite de registros a devolver.
     * @return array Arreglo de registros de socios.
     */
    public function getReporteSocios($filtros = [], $offset = 0, $limit = 0)
    {
        // Usamos la vista v_acciones_completas para obtener información consolidada
        $query = "SELECT 
                    numero_accion as accion, 
                    titular_nacionalidad as nacionalidad,
                    titular_cedula as cedula, 
                    titular_nombre as nombre, 
                    estado, 
                    titular_email as email,
                    titular_telefono as telefonos,
                    fecha_adquisicion as ficha,
                    titular_sexo as sexo,
                    titular_profesion as profesion
                  FROM v_acciones_completas WHERE 1=1";

        $params = [];

        if (!empty($filtros['estado'])) {
            $query .= " AND estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['sexo'])) {
            $query .= " AND titular_sexo = ?";
            $params[] = $filtros['sexo'];
        }

        if (!empty($filtros['profesion'])) {
            $query .= " AND titular_profesion = ?";
            $params[] = $filtros['profesion'];
        }

        if (!empty($filtros['tipo_accion'])) {
            $query .= " AND tipo_accion = ?";
            $params[] = $filtros['tipo_accion'];
        }

        if (isset($filtros['puede_votar']) && $filtros['puede_votar'] !== '') {
            $query .= " AND puede_votar = ?";
            $params[] = $filtros['puede_votar'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND fecha_adquisicion >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND fecha_adquisicion <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        // Filtro específico por Acción (Exacto)
        if (!empty($filtros['accion'])) {
            $query .= " AND numero_accion = ?";
            $params[] = trim($filtros['accion']);
        }

        // Filtro específico por Cédula
        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND titular_cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw); // Quitar prefijo V-, E-, P-

            $query .= " AND (";
            $subQueries = [];

            // 1. Nombre Completo (parcial)
            $subQueries[] = "titular_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            // 2. Cédula (con o sin prefijo)
            $subQueries[] = "titular_cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            // 3. Número de Acción
            $subQueries[] = "numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            // 4. Búsqueda por palabras (Smarter Search)
            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "titular_nombre LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $query .= " ORDER BY numero_accion ASC";

        if ($limit > 0) {
            $query .= " LIMIT $offset, $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de socios que coinciden con los filtros proporcionados, útil para la paginación.
     * 
     * @param array $filtros Arreglo asociativo con los filtros a aplicar.
     * @return int Total de registros encontrados.
     */
    public function countReporteSocios($filtros = [])
    {
        $query = "SELECT COUNT(*) as total FROM v_acciones_completas WHERE 1=1";
        $params = [];

        if (!empty($filtros['estado'])) {
            $query .= " AND estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['sexo'])) {
            $query .= " AND titular_sexo = ?";
            $params[] = $filtros['sexo'];
        }

        if (!empty($filtros['profesion'])) {
            $query .= " AND titular_profesion = ?";
            $params[] = $filtros['profesion'];
        }

        if (!empty($filtros['tipo_accion'])) {
            $query .= " AND tipo_accion = ?";
            $params[] = $filtros['tipo_accion'];
        }

        if (isset($filtros['puede_votar']) && $filtros['puede_votar'] !== '') {
            $query .= " AND puede_votar = ?";
            $params[] = $filtros['puede_votar'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $query .= " AND fecha_adquisicion >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query .= " AND fecha_adquisicion <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        // Filtro específico por Acción (Exacto)
        if (!empty($filtros['accion'])) {
            $query .= " AND numero_accion = ?";
            $params[] = trim($filtros['accion']);
        }

        // Filtro específico por Cédula
        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND titular_cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "titular_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "titular_cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            $subQueries[] = "numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "titular_nombre LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene la distribución de socios agrupados por sexo.
     * 
     * @return array Arreglo con el total de socios por cada sexo.
     */
    public function getSociosPorSexo()
    {
        // Obtenemos sexo desde la tabla personas vinculada a las acciones
        $query = "SELECT p.sexo, COUNT(*) as total 
                  FROM acciones a
                  INNER JOIN personas p ON a.persona_id = p.id
                  WHERE p.sexo IS NOT NULL
                  GROUP BY p.sexo";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la distribución de socios agrupados por rangos de edad.
     * 
     * @return array Arreglo con el total de socios por cada rango de edad.
     */
    public function getSociosPorEdad()
    {
        $query = "SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) < 30 THEN '18-29'
                        WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN 30 AND 39 THEN '30-39'
                        WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 49 THEN '40-49'
                        WHEN TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) BETWEEN 50 AND 59 THEN '50-59'
                        ELSE '60+'
                    END as rango_edad,
                    COUNT(*) as total
                  FROM acciones a
                  INNER JOIN personas p ON a.persona_id = p.id
                  WHERE p.fecha_nacimiento IS NOT NULL
                  GROUP BY rango_edad
                  ORDER BY rango_edad";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== REPORTES DE VOTACIONES ====================

    /**
     * Obtiene las fechas de inicio y fin del periodo de votación actual.
     * 
     * @return array|false Arreglo asociativo con las fechas o false si no hay registro.
     */
    public function getFechasVotacion()
    {
        $query = "SELECT * FROM fechasv LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los resultados consolidados de la votación actual por candidato.
     * 
     * @return array Arreglo con los resultados de votación ordenados por tipo de miembro y cantidad de votos.
     */
    public function getResultadosVotacion()
    {
        $query = "SELECT 
                    p.cedula, 
                    CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) as nombre,
                    p.tipomiembro, p.foto,
                    COUNT(v.voto) as votos
                  FROM postulados p
                  LEFT JOIN votacion v ON p.cedula = v.cedula
                  GROUP BY p.cedula, p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido, p.tipomiembro, p.foto
                  ORDER BY p.tipomiembro, votos DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas específicas del proceso de votación (total votos, participación, etc.).
     * 
     * @return array Arreglo asociativo con las estadísticas de votación.
     */
    public function getEstadisticasVotacion()
    {
        $stats = [];

        // Total de votos emitidos
        $query = "SELECT COUNT(*) as total FROM votacion";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_votos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de socios que votaron
        $query = "SELECT COUNT(DISTINCT accion) as total FROM votacion";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['socios_votaron'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de socios activos
        $query = "SELECT COUNT(*) as total FROM acciones WHERE estado = 'ACTIVO'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['socios_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Calcular porcentaje de participación
        if ($stats['socios_activos'] > 0) {
            $stats['porcentaje_participacion'] = round(($stats['socios_votaron'] / $stats['socios_activos']) * 100, 2);
        } else {
            $stats['porcentaje_participacion'] = 0;
        }

        return $stats;
    }

    /**
     * Obtiene la distribución de votos emitidos agrupados por hora del día.
     * 
     * @return array Arreglo con el total de votos por cada hora.
     */
    public function getVotosPorHora()
    {
        $query = "SELECT 
                    HOUR(fecha) as hora, 
                    COUNT(*) as total 
                  FROM votacion 
                  GROUP BY hora 
                  ORDER BY hora";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== REPORTES DE CÓNYUGES ====================

    /**
     * Obtiene el reporte detallado de cónyuges basado en los filtros proporcionados.
     * 
     * @param array $filtros Arreglo asociativo con los filtros a aplicar.
     * @param int $offset Desplazamiento para la paginación.
     * @param int $limit Límite de registros a devolver.
     * @return array Arreglo de registros de cónyuges.
     */
    public function getReporteConyugues($filtros = [], $offset = 0, $limit = 0)
    {
        $query = "SELECT 
                    conyuge_nacionalidad as nacionalidad,
                    conyuge_cedula as cedula, 
                    conyuge_nombre as nombre, 
                    tipo_relacion as estadocivil, 
                    estado as estadoaccion,
                    titular_nombre as nombre_socio, 
                    numero_accion as accion
                  FROM v_acciones_completas
                  WHERE conyuge_cedula IS NOT NULL";

        $params = [];

        if (!empty($filtros['estadoaccion'])) {
            $query .= " AND estado = ?";
            $params[] = $filtros['estadoaccion'];
        }

        if (!empty($filtros['estadocivil'])) {
            $query .= " AND tipo_relacion = ?";
            $params[] = $filtros['estadocivil'];
        }

        if (!empty($filtros['accion'])) {
            $query .= " AND numero_accion = ?";
            $params[] = trim($filtros['accion']);
        }

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND (conyuge_cedula LIKE ? OR titular_cedula LIKE ?)";
            $params[] = '%' . $cedulaLimpia . '%';
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "conyuge_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "titular_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "conyuge_cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            $subQueries[] = "numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "(conyuge_nombre LIKE ? OR titular_nombre LIKE ?)";
                    $params[] = '%' . $word . '%';
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $query .= " ORDER BY conyuge_nombre ASC";

        if ($limit > 0) {
            $query .= " LIMIT $offset, $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de cónyuges que coinciden con los filtros proporcionados, útil para la paginación.
     * 
     * @param array $filtros Arreglo asociativo con los filtros a aplicar.
     * @return int Total de registros encontrados.
     */
    public function countReporteConyugues($filtros = [])
    {
        $query = "SELECT COUNT(*) as total FROM v_acciones_completas WHERE conyuge_cedula IS NOT NULL";
        $params = [];

        if (!empty($filtros['estadoaccion'])) {
            $query .= " AND estado = ?";
            $params[] = $filtros['estadoaccion'];
        }

        if (!empty($filtros['estadocivil'])) {
            $query .= " AND tipo_relacion = ?";
            $params[] = $filtros['estadocivil'];
        }

        if (!empty($filtros['accion'])) {
            $query .= " AND numero_accion = ?";
            $params[] = trim($filtros['accion']);
        }

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND (conyuge_cedula LIKE ? OR titular_cedula LIKE ?)";
            $params[] = '%' . $cedulaLimpia . '%';
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "conyuge_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "titular_nombre LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "conyuge_cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            $subQueries[] = "numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "(conyuge_nombre LIKE ? OR titular_nombre LIKE ?)";
                    $params[] = '%' . $word . '%';
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getConyuguesPorEstadoAccion()
    {
        $query = "SELECT a.estado as estadoaccion, COUNT(*) as total 
                  FROM acciones a
                  INNER JOIN conyugue c ON a.id = c.accion_id
                  GROUP BY a.estado";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== REPORTES DE USUARIOS ====================

    public function getReporteUsuarios()
    {
        $query = "SELECT u.usuario, tu.nombre as tipousuario, pt.nombre as puestotrabajo 
                  FROM usuarios u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  ORDER BY tu.nombre, u.usuario ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuariosPorTipo()
    {
        $query = "SELECT tu.nombre as tipousuario, COUNT(*) as total 
                  FROM usuarios u
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  GROUP BY tu.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== REPORTES DE HUELLAS ====================

    public function getReporteHuellas($filtros = [], $offset = 0, $limit = 0)
    {
        $query = "SELECT 
                    p.id, p.cedula, p.updated_at as created_at,
                    TRIM(CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido)) as nombre, a.numero_accion as accion
                   FROM personas p
                   INNER JOIN acciones a ON a.persona_id = p.id
                   WHERE p.huella_template IS NOT NULL AND p.huella_template != ''";

        $params = [];

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND p.cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "a.numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $query .= " ORDER BY p.updated_at DESC";

        if ($limit > 0) {
            $query .= " LIMIT $offset, $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countReporteHuellas($filtros = [])
    {
        $query = "SELECT COUNT(DISTINCT p.id) as total 
                  FROM personas p
                  INNER JOIN acciones a ON a.persona_id = p.id
                  WHERE p.huella_template IS NOT NULL AND p.huella_template != ''";
        $params = [];

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND p.cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "a.numero_accion LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getPorcentajeHuellas()
    {
        $stats = [];

        // Total de socios (acciones)
        $query = "SELECT COUNT(*) as total FROM acciones";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_socios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Socios con huella (titulares)
        $query = "SELECT COUNT(DISTINCT p.id) as total 
                  FROM personas p
                  INNER JOIN acciones a ON a.persona_id = p.id
                  WHERE p.huella_template IS NOT NULL AND p.huella_template != ''";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['con_huella'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Sin huella
        $stats['sin_huella'] = $stats['total_socios'] - $stats['con_huella'];

        // Porcentaje
        if ($stats['total_socios'] > 0) {
            $stats['porcentaje_con_huella'] = round(($stats['con_huella'] / $stats['total_socios']) * 100, 2);
        } else {
            $stats['porcentaje_con_huella'] = 0;
        }

        return $stats;
    }
    // ==================== REPORTES DE POSTULADOS ====================

    public function getReportePostulados($filtros = [], $offset = 0, $limit = 0)
    {
        $query = "SELECT 
                    p.tipomiembro, p.cedula,
                    CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) as nombre,
                    p.profesion, p.correo_electronico, p.tipo_referencia, p.referencia_id, p.en_votacion,
                    n.codigo as nacionalidad_codigo
                  FROM postulados p
                  LEFT JOIN nacionalidades n ON p.nacionalidad_id = n.id
                  WHERE 1=1";

        $params = [];

        if (!empty($filtros['tipomiembro'])) {
            $query .= " AND p.tipomiembro = ?";
            $params[] = $filtros['tipomiembro'];
        }

        if (isset($filtros['en_votacion']) && $filtros['en_votacion'] !== '') {
            $query .= " AND p.en_votacion = ?";
            $params[] = $filtros['en_votacion'];
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "p.cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND p.cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        $query .= " ORDER BY p.tipomiembro, p.primer_apellido, p.primer_nombre ASC";

        if ($limit > 0) {
            $query .= " LIMIT $offset, $limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countReportePostulados($filtros = [])
    {
        $query = "SELECT COUNT(*) as total FROM postulados p WHERE 1=1";
        $params = [];

        if (!empty($filtros['tipomiembro'])) {
            $query .= " AND p.tipomiembro = ?";
            $params[] = $filtros['tipomiembro'];
        }

        if (isset($filtros['en_votacion']) && $filtros['en_votacion'] !== '') {
            $query .= " AND p.en_votacion = ?";
            $params[] = $filtros['en_votacion'];
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $busquedaLimpia = preg_replace('/^[VEP]-/i', '', $busquedaRaw);

            $query .= " AND (";
            $subQueries = [];

            $subQueries[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
            $params[] = '%' . $busquedaRaw . '%';

            $subQueries[] = "p.cedula LIKE ?";
            $params[] = '%' . $busquedaLimpia . '%';

            $words = array_filter(explode(' ', $busquedaRaw), function ($w) {
                return strlen($w) > 2; });
            if (count($words) > 1) {
                $wordParts = [];
                foreach ($words as $word) {
                    $wordParts[] = "CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) LIKE ?";
                    $params[] = '%' . $word . '%';
                }
                $subQueries[] = "(" . implode(" AND ", $wordParts) . ")";
            }

            $query .= implode(" OR ", $subQueries) . ")";
        }

        if (!empty($filtros['cedula'])) {
            $cedulaLimpia = preg_replace('/^[VEP]-/i', '', trim($filtros['cedula']));
            $query .= " AND p.cedula LIKE ?";
            $params[] = '%' . $cedulaLimpia . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getEstadisticasPostulados()
    {
        $stats = [];

        // Total de postulados
        $query = "SELECT COUNT(*) as total FROM postulados";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_postulados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Por tipo de miembro
        $query = "SELECT tipomiembro, COUNT(*) as total FROM postulados GROUP BY tipomiembro";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['por_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // En votación
        $query = "SELECT COUNT(*) as total FROM postulados WHERE en_votacion = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['en_votacion'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }
}
?>