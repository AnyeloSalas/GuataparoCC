<?php

class Votacion
{
    private $conn;
    private $table_votacion = "votacion";
    private $table_fechas = "fechasv";
    private $table_postulados = "postulados";

    public $voto;
    public $accion;
    public $cedula;
    public $tipovoto;
    public $fecha;
    public $es_presencial = 1;

    // Fechas de votación
    public $iniciov;
    public $finv;
    public $iniciop;
    public $finp;

    /**
     * Constructor de la clase Votacion.
     * 
     * @param PDO $db Conexión a la base de datos.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo voto en la base de datos.
     * Incluye un mecanismo de autorreparación si falta la columna 'es_presencial'.
     * 
     * @return bool True si el voto se registró correctamente.
     * @throws PDOException Si ocurre un error en la consulta.
     */
    public function registrarVoto()
    {
        try {
            $query = "INSERT INTO " . $this->table_votacion . " 
                      SET voto=:voto, accion=:accion, cedula=:cedula, tipovoto=:tipovoto, fecha=:fecha, es_presencial=:es_presencial";
            $stmt = $this->conn->prepare($query);

            $this->accion = htmlspecialchars(strip_tags($this->accion));
            $this->cedula = htmlspecialchars(strip_tags($this->cedula));
            $this->tipovoto = htmlspecialchars(strip_tags($this->tipovoto));

            $stmt->bindParam(":voto", $this->voto);
            $stmt->bindParam(":accion", $this->accion);
            $stmt->bindParam(":cedula", $this->cedula);
            $stmt->bindParam(":tipovoto", $this->tipovoto);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":es_presencial", $this->es_presencial);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Si el error es "Column not found" (1054), intentamos repararlo automáticamente
            if ($e->getCode() == '42S22' || strpos($e->getMessage(), 'Unknown column \'es_presencial\'') !== false) {
                try {
                    $this->conn->exec("ALTER TABLE `" . $this->table_votacion . "` ADD COLUMN `es_presencial` TINYINT(1) NOT NULL DEFAULT 1");
                    // Reintentar una vez
                    return $this->registrarVoto();
                } catch (Exception $ex) {
                    error_log("Error de autorreparación: " . $ex->getMessage());
                    throw $e; // Re-lanzar el error original si la reparación falla
                }
            }
            throw $e;
        }
    }

    /**
     * Obtiene el rango de fechas con horas para consultas DATETIME (desde 00:00:00 del inicio hasta 23:59:59 del fin).
     * Evita descartar los votos emitidos el último día del periodo de votación.
     * 
     * @return array|false ['inicio' => 'YYYY-MM-DD 00:00:00', 'fin' => 'YYYY-MM-DD 23:59:59'] o false
     */
    public function getRangoFechasVotacion()
    {
        $fechas = $this->getFechas();
        if (!$fechas || empty($fechas['iniciov']) || empty($fechas['finv'])) {
            return false;
        }

        $inicio = trim(explode(' ', $fechas['iniciov'])[0]) . ' 00:00:00';
        $fin = trim(explode(' ', $fechas['finv'])[0]) . ' 23:59:59';

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'iniciov' => $fechas['iniciov'],
            'finv' => $fechas['finv']
        ];
    }

    /**
     * Verifica si una acción (socio) ya votó por un candidato específico o en general durante el periodo actual.
     * 
     * @param string $accion Número de acción del socio.
     * @param string|null $cedula_candidato (Opcional) Cédula del candidato a verificar.
     * @return bool True si ya votó, False en caso contrario.
     */
    public function yaVoto($accion, $cedula_candidato = null)
    {
        $rango = $this->getRangoFechasVotacion();
        if (!$rango)
            return false;

        if ($cedula_candidato) {
            // Verificar si ya votó por un candidato específico
            $query = "SELECT COUNT(*) as total FROM " . $this->table_votacion . " 
                      WHERE accion = ? AND cedula = ? AND fecha BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $accion);
            $stmt->bindParam(2, $cedula_candidato);
            $stmt->bindParam(3, $rango['inicio']);
            $stmt->bindParam(4, $rango['fin']);
        } else {
            // Verificar si ya votó en general
            $query = "SELECT COUNT(*) as total FROM " . $this->table_votacion . " 
                      WHERE accion = ? AND fecha BETWEEN ? AND ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $accion);
            $stmt->bindParam(2, $rango['inicio']);
            $stmt->bindParam(3, $rango['fin']);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    /**
     * Verifica si una acción ya completó toda su votación (votó por todos los postulados) en el periodo actual.
     * 
     * @param string $accion Número de acción del socio.
     * @param int $total_postulados Total de postulados habilitados para votación.
     * @return bool True si ya completó su votación.
     */
    public function accionYaVotoCompleto($accion, $total_postulados)
    {
        $rango = $this->getRangoFechasVotacion();
        if (!$rango)
            return false;

        $query = "SELECT COUNT(*) as total FROM " . $this->table_votacion . " 
                  WHERE accion = ? AND fecha BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $accion);
        $stmt->bindParam(2, $rango['inicio']);
        $stmt->bindParam(3, $rango['fin']);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] >= $total_postulados;
    }

    // --- VOTO REMOTO METODOS ---
    public function generarTokenVotoRemoto($accion, $horas_validez = 24)
    {
        $token_plano = bin2hex(random_bytes(16)); // 32 caracteres hex
        $token_hash = hash('sha256', $token_plano);
        $expiracion = date('Y-m-d H:i:s', strtotime("+$horas_validez hours"));

        $query = "INSERT INTO voto_remoto_tokens SET accion = :accion, token_hash = :hash, expiracion = :exp";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":accion", $accion);
        $stmt->bindParam(":hash", $token_hash);
        $stmt->bindParam(":exp", $expiracion);

        if ($stmt->execute()) {
            return $token_plano;
        }
        return false;
    }

    public function validarTokenVotoRemoto($token_plano, $forUpdate = false)
    {
        $token_hash = hash('sha256', $token_plano);
        $query = "SELECT id, accion, expiracion, usado FROM voto_remoto_tokens WHERE token_hash = :hash" . ($forUpdate ? " FOR UPDATE" : "") . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hash", $token_hash);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['usado'] == 1) {
                return ['valido' => false, 'error' => 'El enlace ya fue utilizado para votar.'];
            }
            if ($row['expiracion'] < date('Y-m-d H:i:s')) {
                return ['valido' => false, 'error' => 'El enlace ha expirado.'];
            }
            return ['valido' => true, 'id_token' => $row['id'], 'accion' => $row['accion']];
        }
        return ['valido' => false, 'error' => 'Enlace no válido.'];
    }

    public function marcarTokenUsado($id_token)
    {
        $query = "UPDATE voto_remoto_tokens SET usado = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id_token);
        return $stmt->execute();
    }
    // --- FIN VOTO REMOTO METODOS ---

    /**
     * Obtiene las fechas de inicio y fin del periodo de votación actual.
     * 
     * @return array|false Arreglo asociativo con las fechas o false si no hay registro.
     */
    public function getFechas()
    {
        $query = "SELECT * FROM " . $this->table_fechas . " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar fechas de votación
    public function actualizarFechas()
    {
        // Primero eliminar registros existentes
        $delete = "DELETE FROM " . $this->table_fechas;
        $this->conn->prepare($delete)->execute();

        // Insertar nuevas fechas (solo votación, postulación eliminada)
        $query = "INSERT INTO " . $this->table_fechas . " 
                  SET iniciov=:iniciov, finv=:finv";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":iniciov", $this->iniciov);
        $stmt->bindParam(":finv", $this->finv);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Verifica si el sistema se encuentra actualmente en un período de votación o postulación activo.
     * 
     * @param string $tipo Tipo de periodo a verificar ('votacion' o 'postulacion').
     * @return bool True si está en periodo activo, False en caso contrario.
     */
    public function enPeriodoVotacion($tipo = 'votacion')
    {
        $fechas = $this->getFechas();
        if (!$fechas)
            return false;

        $hoy = date('Y-m-d');

        if ($tipo == 'votacion') {
            $enFecha = ($hoy >= $fechas['iniciov'] && $hoy <= $fechas['finv']);

            if (!$enFecha)
                return false;

            // Requisito adicional: Debe haber al menos un postulado habilitado para votación
            // Esto permite que al "Finalizar Proceso" (donde se quitan los postulados)
            // el sistema marque "Fuera de Período" aunque la fecha siga vigente.
            $query = "SELECT COUNT(*) as total FROM " . $this->table_postulados . " WHERE en_votacion = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($count['total'] > 0);

        } else if ($tipo == 'postulacion') {
            return ($hoy >= $fechas['iniciop'] && $hoy <= $fechas['finp']);
        }

        return false;
    }

    /**
     * Obtiene la lista de todos los postulados registrados.
     * 
     * @return PDOStatement Objeto con los resultados de la consulta.
     */
    public function getPostulados()
    {
        $query = "SELECT * FROM " . $this->table_postulados . " ORDER BY tipomiembro, nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener resultados del periodo actual
    public function getResultados()
    {
        $rango = $this->getRangoFechasVotacion();
        if (!$rango) {
            // Retornar statement vacio o manejar error
            return null;
        }

        $query = "SELECT tipovoto, COUNT(*) as total 
                  FROM " . $this->table_votacion . " 
                  WHERE fecha BETWEEN ? AND ?
                  GROUP BY tipovoto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $rango['inicio']);
        $stmt->bindParam(2, $rango['fin']);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene los votos por candidato con un desglose de Aceptados, Rechazados y Otros en el periodo actual.
     * 
     * @param string|null $tipomiembro Filtro opcional por tipo de miembro.
     * @param bool $solo_en_votacion Si es true, solo incluye postulados marcados como 'en_votacion'.
     * @return PDOStatement Objeto con los resultados de la consulta.
     */
    public function getVotosPorCandidato($tipomiembro = null, $solo_en_votacion = false)
    {
        $rango = $this->getRangoFechasVotacion();
        // Si no hay fechas definidas, no filtramos por fecha (o retornamos 0)
        // Asumiendo que siempre hay fechas si hay votación activa.

        $query = "SELECT 
                    p.cedula,
                    CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) as nombre, 
                    p.tipomiembro, 
                    p.foto,
                    COUNT(v.cedula) as votos_totales,
                    SUM(CASE WHEN v.tipovoto = 'ACEPTADO' THEN 1 ELSE 0 END) as aceptados,
                    SUM(CASE WHEN v.tipovoto = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados,
                    SUM(CASE WHEN v.tipovoto NOT IN ('ACEPTADO', 'RECHAZADO') AND v.tipovoto IS NOT NULL THEN 1 ELSE 0 END) as otros,
                    p.en_votacion
                  FROM " . $this->table_postulados . " p
                  LEFT JOIN " . $this->table_votacion . " v ON p.cedula = v.cedula ";

        if ($rango) {
            $query .= " AND v.fecha BETWEEN '" . $rango['inicio'] . "' AND '" . $rango['fin'] . "'";
        }

        $query .= " WHERE 1=1 ";
        if ($solo_en_votacion) {
            $query .= " AND p.en_votacion = 1 ";
        }

        if ($tipomiembro) {
            $query .= " AND p.tipomiembro = ? ";
        }

        $query .= " GROUP BY p.cedula, p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido, p.tipomiembro, p.foto, p.en_votacion
                    ORDER BY p.tipomiembro, aceptados DESC";

        $stmt = $this->conn->prepare($query);
        if ($tipomiembro) {
            $stmt->bindParam(1, $tipomiembro);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getResultadoPorCedula($cedula)
    {
        $rango = $this->getRangoFechasVotacion();

        $query = "SELECT 
                    SUM(CASE WHEN tipovoto = 'ACEPTADO' THEN 1 ELSE 0 END) as aceptados,
                    SUM(CASE WHEN tipovoto = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados
                  FROM " . $this->table_votacion . " 
                  WHERE cedula = ?";

        if ($rango) {
            $query .= " AND fecha BETWEEN ? AND ?";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cedula);

        if ($rango) {
            $stmt->bindParam(2, $rango['inicio']);
            $stmt->bindParam(3, $rango['fin']);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas generales del periodo de votación actual (total votos, votantes únicos, participación).
     * 
     * @return array Arreglo asociativo con las estadísticas.
     */
    public function getEstadisticas()
    {
        $stats = [];
        $rango = $this->getRangoFechasVotacion();

        // Total de votos
        $query = "SELECT COUNT(*) as total FROM " . $this->table_votacion;
        if ($rango) {
            $query .= " WHERE fecha BETWEEN '" . $rango['inicio'] . "' AND '" . $rango['fin'] . "'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_votos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de votantes únicos
        $query = "SELECT COUNT(DISTINCT accion) as total FROM " . $this->table_votacion;
        if ($rango) {
            $query .= " WHERE fecha BETWEEN '" . $rango['inicio'] . "' AND '" . $rango['fin'] . "'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['votantes_unicos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de socios
        $query = "SELECT COUNT(*) as total FROM acciones WHERE estado = 'ACTIVO'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_socios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Participación
        if ($stats['total_socios'] > 0) {
            $stats['participacion'] = round(($stats['votantes_unicos'] / $stats['total_socios']) * 100, 2);
        } else {
            $stats['participacion'] = 0;
        }

        return $stats;
    }
    // Limpiar votos del periodo actual
    public function limpiarVotosPeriodo()
    {
        $rango = $this->getRangoFechasVotacion();
        if (!$rango)
            return false;

        $query = "DELETE FROM " . $this->table_votacion . " 
                  WHERE fecha BETWEEN :iniciov AND :finv";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":iniciov", $rango['inicio']);
        $stmt->bindParam(":finv", $rango['fin']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // --- METODOS PARA EL HISTORICO ---

    /**
     * Archiva el proceso de votación actual antes de finalizarlo, guardando los resultados en el histórico.
     * Utiliza transacciones para asegurar la integridad de los datos.
     * 
     * @param string $nombre_periodo Nombre descriptivo para el periodo a archivar.
     * @return bool True si se archivó correctamente, False en caso de error.
     */
    public function archivarPeriodo($nombre_periodo)
    {
        try {
            $this->conn->beginTransaction();

            $fechas = $this->getFechas();
            $stats = $this->getEstadisticas();

            // 1. Crear el periodo en el histórico
            $query_p = "INSERT INTO votacion_periodos 
                        SET nombre = :nombre, fecha_inicio = :inicio, fecha_fin = :fin, 
                            total_votos = :votos, total_votantes = :votantes";
            $stmt_p = $this->conn->prepare($query_p);
            $stmt_p->execute([
                ':nombre' => $nombre_periodo,
                ':inicio' => $fechas['iniciov'],
                ':fin' => $fechas['finv'],
                ':votos' => $stats['total_votos'],
                ':votantes' => $stats['votantes_unicos']
            ]);

            $periodo_id = $this->conn->lastInsertId();

            // 2. Obtener resultados de todos los candidatos que estuvieron en votación
            $stmt_r = $this->getVotosPorCandidato(null, true);
            $resultados = $stmt_r->fetchAll(PDO::FETCH_ASSOC);

            // 3. Guardar resultados de cada candidato
            $query_res = "INSERT INTO votacion_resultados_historico 
                          SET periodo_id = :pid, cedula_candidato = :cedula, nombre_candidato = :nombre,
                              tipomiembro = :tipo, votos_aceptados = :acep, votos_rechazados = :rech,
                              votos_otros = :otros, es_ganador = :ganador";
            $stmt_res = $this->conn->prepare($query_res);

            require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
            $configModel = new Configuracion($this->conn);
            $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
            $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

            $quorum_alcanzado = ($stats['votantes_unicos'] >= $minimo_votantes);

            foreach ($resultados as $row) {
                // Consideramos ganador si cumple con el porcentaje de aceptación sobre los votos emitidos
                // y se alcanzó el quórum mínimo de votantes
                $total_votos_cand = (int) $row['votos_totales'];
                $votos_req = ceil(($total_votos_cand * $porcentaje_req) / 100);
                $es_ganador = ($quorum_alcanzado && (int) $row['aceptados'] >= $votos_req && $total_votos_cand > 0) ? 1 : 0;

                $stmt_res->execute([
                    ':pid' => $periodo_id,
                    ':cedula' => $row['cedula'],
                    ':nombre' => $row['nombre'],
                    ':tipo' => $row['tipomiembro'],
                    ':acep' => $row['aceptados'],
                    ':rech' => $row['rechazados'],
                    ':otros' => $row['otros'],
                    ':ganador' => $es_ganador
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al archivar periodo de votación: " . $e->getMessage());
            return false;
        }
    }

    // Obtener lista de periodos archivados
    public function getHistoricoPeriodos($fecha_desde = null, $fecha_hasta = null)
    {
        $query = "SELECT * FROM votacion_periodos WHERE 1=1";
        $params = [];

        if (!empty($fecha_desde)) {
            $query .= " AND DATE(fecha_cierre) >= ?";
            $params[] = $fecha_desde;
        }
        if (!empty($fecha_hasta)) {
            $query .= " AND DATE(fecha_cierre) <= ?";
            $params[] = $fecha_hasta;
        }

        $query .= " ORDER BY fecha_cierre DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener detalle de resultados de un periodo específico
    public function getResultadosHistoricos($periodo_id, $filtros = [])
    {
        $query = "SELECT * FROM votacion_resultados_historico 
                  WHERE periodo_id = ?";

        $params = [$periodo_id];

        if (!empty($filtros['tipomiembro'])) {
            $query .= " AND tipomiembro = ?";
            $params[] = $filtros['tipomiembro'];
        }

        if (!empty($filtros['busqueda'])) {
            $busquedaRaw = trim($filtros['busqueda']);
            $query .= " AND (nombre_candidato LIKE ? OR cedula_candidato LIKE ?)";
            $params[] = '%' . $busquedaRaw . '%';
            $params[] = '%' . $busquedaRaw . '%';
        }

        $query .= " ORDER BY tipomiembro, votos_aceptados DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>