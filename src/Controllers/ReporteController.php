<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Reporte.php';
require_once __DIR__ . '/../Models/Profesion.php';

/**
 * Controlador de Reportes (ReporteController)
 * 
 * Gestiona la generación, visualización y exportación de todos los reportes
 * del sistema, incluyendo socios, votaciones, cónyuges, usuarios, huellas y postulados.
 */
class ReporteController
{
    /** @var PDO|null Conexión a la base de datos */
    private $db;

    /** @var Reporte Instancia del modelo Reporte */
    private $reporte;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reporte = new Reporte($this->db);
    }

    /**
     * Muestra el dashboard principal de reportes con estadísticas generales y gráficos.
     * 
     * @return void
     */
    public function index()
    {
        $this->checkAuth();

        // Obtener estadísticas generales
        $estadisticas = $this->reporte->getEstadisticasGenerales();

        // Obtener datos para gráficos
        $sociosPorEstado = $this->reporte->getEstadisticasGenerales()['socios_por_estado'];
        $sociosPorSexo = $this->reporte->getSociosPorSexo();
        $usuariosPorTipo = $this->reporte->getUsuariosPorTipo();
        $porcentajeHuellas = $this->reporte->getPorcentajeHuellas();

        require_once __DIR__ . '/../Views/reportes/index.php';
    }

    /**
     * Muestra el reporte detallado de socios.
     * Maneja filtros de búsqueda, paginación y carga de datos relacionados.
     * 
     * @return void
     */
    public function socios()
    {
        $this->checkAuth();

        // Parámetros de paginación
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limite = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($pagina - 1) * $limite;

        // Obtener filtros
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'sexo' => $_GET['sexo'] ?? '',
            'profesion' => $_GET['profesion'] ?? '',
            'tipo_accion' => $_GET['tipo_accion'] ?? '',
            'puede_votar' => isset($_GET['puede_votar']) ? $_GET['puede_votar'] : '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'cedula' => $_GET['cedula'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        // Verificar si se hizo una búsqueda (si alguno de los filtros tiene valor)
        $is_searching = false;
        foreach ($filtros as $key => $value) {
            if ($value !== '') {
                $is_searching = true;
                break;
            }
        }

        // Obtener tipos de acción para el filtro
        $stmt_tipos = $this->db->query("SELECT DISTINCT tipo_accion FROM acciones WHERE tipo_accion IS NOT NULL AND tipo_accion != '' ORDER BY tipo_accion ASC");
        $tipos_accion = $stmt_tipos->fetchAll(PDO::FETCH_COLUMN);

        // Obtener profesiones para el filtro
        $profesionModel = new Profesion($this->db);
        $profesiones = $profesionModel->readActive()->fetchAll(PDO::FETCH_ASSOC);

        if ($is_searching) {
            // Obtener datos paginados solo si hay búsqueda
            $socios = $this->reporte->getReporteSocios($filtros, $offset, $limite);
            $total_registros = $this->reporte->countReporteSocios($filtros);
            $total_paginas = ceil($total_registros / $limite);
        } else {
            $socios = [];
            $total_registros = 0;
            $total_paginas = 0;
        }

        $estadisticas = $this->reporte->getEstadisticasGenerales();
        $sociosPorSexo = $this->reporte->getSociosPorSexo();

        require_once __DIR__ . '/../Views/reportes/socios.php';
    }

    /**
     * Muestra el reporte de votaciones.
     * Incluye estadísticas, resultados por candidato y distribución de votos por hora.
     * 
     * @return void
     */
    public function votaciones()
    {
        $this->checkAuth();

        // Obtener datos
        $fechas = $this->reporte->getFechasVotacion();
        $resultados = $this->reporte->getResultadosVotacion();
        $estadisticas = $this->reporte->getEstadisticasVotacion();
        $votosPorHora = $this->reporte->getVotosPorHora();

        require_once __DIR__ . '/../Views/reportes/votaciones.php';
    }

    /**
     * Muestra el reporte de familiares/cónyuges.
     * Maneja filtros de búsqueda y paginación.
     * 
     * @return void
     */
    public function conyugues()
    {
        $this->checkAuth();

        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limite = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($pagina - 1) * $limite;

        // Obtener filtros
        $filtros = [
            'estadoaccion' => $_GET['estadoaccion'] ?? '',
            'estadocivil' => $_GET['estadocivil'] ?? '',
            'cedula' => $_GET['cedula'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        $is_searching = false;
        foreach ($filtros as $key => $value) {
            if ($value !== '') {
                $is_searching = true;
                break;
            }
        }

        if ($is_searching) {
            // Obtener datos
            $conyugues = $this->reporte->getReporteConyugues($filtros, $offset, $limite);
            $total_registros = $this->reporte->countReporteConyugues($filtros);
            $total_paginas = ceil($total_registros / $limite);
        } else {
            $conyugues = [];
            $total_registros = 0;
            $total_paginas = 0;
        }
        $estadisticas = $this->reporte->getEstadisticasGenerales();
        $conyuguesPorEstado = $this->reporte->getConyuguesPorEstadoAccion();

        require_once __DIR__ . '/../Views/reportes/conyugues.php';
    }

    /**
     * Muestra el reporte de usuarios del sistema y sus roles.
     * 
     * @return void
     */
    public function usuarios()
    {
        $this->checkAuth();

        // Obtener datos
        $usuarios = $this->reporte->getReporteUsuarios();
        $usuariosPorTipo = $this->reporte->getUsuariosPorTipo();
        $estadisticas = $this->reporte->getEstadisticasGenerales();

        require_once __DIR__ . '/../Views/reportes/usuarios.php';
    }

    /**
     * Muestra el reporte de registro biométrico (huellas dactilares).
     * Maneja filtros y paginación para auditar quiénes tienen huella registrada.
     * 
     * @return void
     */
    public function huellas()
    {
        $this->checkAuth();

        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limite = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($pagina - 1) * $limite;

        $filtros = [
            'cedula' => $_GET['cedula'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        $is_searching = false;
        foreach ($filtros as $key => $value) {
            if ($value !== '') {
                $is_searching = true;
                break;
            }
        }

        if ($is_searching) {
            // Obtener datos
            $huellas = $this->reporte->getReporteHuellas($filtros, $offset, $limite);
            $total_registros = $this->reporte->countReporteHuellas($filtros);
            $total_paginas = ceil($total_registros / $limite);
        } else {
            $huellas = [];
            $total_registros = 0;
            $total_paginas = 0;
        }
        $porcentajeHuellas = $this->reporte->getPorcentajeHuellas();
        $estadisticas = $this->reporte->getEstadisticasGenerales();

        require_once __DIR__ . '/../Views/reportes/huellas.php';
    }

    /**
     * Muestra el reporte de postulados a la junta directiva.
     * Maneja filtros por tipo de miembro, estado de votación y paginación.
     * 
     * @return void
     */
    public function postulados()
    {
        $this->checkAuth();

        // Parámetros de paginación
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limite = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($pagina - 1) * $limite;

        $filtros = [
            'tipomiembro' => $_GET['tipomiembro'] ?? '',
            'en_votacion' => $_GET['en_votacion'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? '',
            'cedula' => $_GET['cedula'] ?? ''
        ];

        $is_searching = false;
        foreach ($filtros as $key => $value) {
            if ($value !== '') {
                $is_searching = true;
                break;
            }
        }

        if ($is_searching) {
            $postulados = $this->reporte->getReportePostulados($filtros, $offset, $limite);
            $total_registros = $this->reporte->countReportePostulados($filtros);
            $total_paginas = ceil($total_registros / $limite);
        } else {
            $postulados = [];
            $total_registros = 0;
            $total_paginas = 0;
        }

        $estadisticas = $this->reporte->getEstadisticasPostulados();

        require_once __DIR__ . '/../Views/reportes/postulados.php';
    }

    /**
     * Controlador central para la exportación de reportes.
     * Redirige a la función específica de exportación según el parámetro 'tipo'.
     * 
     * @return void
     */
    public function exportar()
    {
        $this->checkAuth();

        $tipo = $_GET['tipo'] ?? 'socios';

        switch ($tipo) {
            case 'socios':
                $this->exportarSocios();
                break;
            case 'votaciones':
                $this->exportarVotaciones();
                break;
            case 'conyugues':
                $this->exportarConyugues();
                break;
            case 'usuarios':
                $this->exportarUsuarios();
                break;
            case 'huellas':
                $this->exportarHuellas();
                break;
            case 'postulados':
                $this->exportarPostulados();
                break;
            default:
                header('Location: /reportes');
                break;
        }
    }

    /**
     * Exporta el reporte de socios en formato imprimible, PDF o Excel
     */
    private function exportarSocios()
    {
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'sexo' => $_GET['sexo'] ?? '',
            'profesion' => $_GET['profesion'] ?? '',
            'tipo_accion' => $_GET['tipo_accion'] ?? '',
            'puede_votar' => isset($_GET['puede_votar']) ? $_GET['puede_votar'] : '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'cedula' => $_GET['cedula'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        $socios = $this->reporte->getReporteSocios($filtros);
        $formato = $_GET['formato'] ?? 'print';

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_socios.csv');

            $output = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['Acción', 'Cédula', 'Nombre', 'Estado', 'Teléfono', 'Email', 'Profesión', 'Sexo'], ';');
            foreach ($socios as $socio) {
                $sexoStr = '-';
                if (($socio['sexo'] ?? '') === 'M')
                    $sexoStr = 'Masculino';
                elseif (($socio['sexo'] ?? '') === 'F')
                    $sexoStr = 'Femenino';

                fputcsv($output, [
                    $socio['accion'],
                    ($socio['nacionalidad'] ?? 'V') . '-' . ($socio['cedula'] ?? ''),
                    $socio['nombre'],
                    $socio['estado'],
                    $socio['telefonos'] ?? '-',
                    $socio['email'] ?? '-',
                    $socio['profesion'] ?? '-',
                    $sexoStr
                ], ';');
            }
            fclose($output);
            exit();
        } else {
            require_once __DIR__ . '/../Views/reportes/exportar/socios.php';
        }
    }

    /**
     * Exporta el reporte de votaciones en formato imprimible
     */
    private function exportarVotaciones()
    {
        $fechas = $this->reporte->getFechasVotacion();
        $resultados = $this->reporte->getResultadosVotacion();
        $estadisticas = $this->reporte->getEstadisticasVotacion();

        $formato = $_GET['formato'] ?? 'print';

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=resultados_votacion_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            fputcsv($output, ['RESULTADOS DEL PROCESO ELECTORAL'], ';');
            fputcsv($output, ['Total Votos', $estadisticas['total_votos']], ';');
            fputcsv($output, ['Socios Habilitados', $estadisticas['socios_activos']], ';');
            fputcsv($output, ['Participación', $estadisticas['porcentaje_participacion'] . '%'], ';');
            fputcsv($output, [], ';');

            fputcsv($output, ['Tipo Miembro', 'Candidato', 'Votos', 'Porcentaje'], ';');

            foreach ($resultados as $candidato) {
                $porcentaje = ($estadisticas['total_votos'] > 0)
                    ? round(($candidato['votos'] / $estadisticas['total_votos']) * 100, 1)
                    : 0;
                fputcsv($output, [
                    $candidato['tipomiembro'],
                    $candidato['nombre'],
                    $candidato['votos'],
                    $porcentaje . '%'
                ], ';');
            }
            fclose($output);
            exit();
        } else {
            require_once __DIR__ . '/../Views/reportes/exportar/votaciones.php';
        }
    }

    /**
     * Exporta el reporte de cónyuges en formato imprimible
     */
    private function exportarConyugues()
    {
        $filtros = [
            'estadoaccion' => $_GET['estadoaccion'] ?? '',
            'estadocivil' => $_GET['estadocivil'] ?? '',
            'cedula' => $_GET['cedula'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        $conyugues = $this->reporte->getReporteConyugues($filtros);

        $formato = $_GET['formato'] ?? 'print';

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_familiares_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para Excel

            fputcsv($output, ['Cédula', 'Nombre', 'Parentesco', 'Estado Civil', 'Estado Acción', 'Socio Titular', 'Acción'], ';');

            foreach ($conyugues as $conyugue) {
                fputcsv($output, [
                    ($conyugue['nacionalidad'] ?? 'V') . '-' . ($conyugue['cedula'] ?? ''),
                    $conyugue['nombre'],
                    'Familiar',
                    $conyugue['estadocivil'],
                    $conyugue['estadoaccion'],
                    $conyugue['nombre_socio'],
                    $conyugue['accion']
                ], ';');
            }
            fclose($output);
            exit();
        } else {
            require_once __DIR__ . '/../Views/reportes/exportar/conyugues.php';
        }
    }

    /**
     * Exporta el reporte de usuarios en formato imprimible
     */
    private function exportarUsuarios()
    {
        $usuarios = $this->reporte->getReporteUsuarios();
        require_once __DIR__ . '/../Views/reportes/exportar/usuarios.php';
    }

    /**
     * Exporta el reporte de huellas en formato imprimible
     */
    private function exportarHuellas()
    {
        $huellas = $this->reporte->getReporteHuellas();
        $porcentajeHuellas = $this->reporte->getPorcentajeHuellas();

        $formato = $_GET['formato'] ?? 'print';

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_huellas_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            fputcsv($output, ['REPORTE DE REGISTRO BIOMÉTRICO (HUELLAS)'], ';');
            fputcsv($output, ['Con Huella', $porcentajeHuellas['con_huella']], ';');
            fputcsv($output, ['Sin Huella', $porcentajeHuellas['sin_huella']], ';');
            fputcsv($output, ['Porcentaje Cobertura', $porcentajeHuellas['porcentaje_con_huella'] . '%'], ';');
            fputcsv($output, [], ';');

            fputcsv($output, ['Fecha Registro', 'Acción', 'Cédula', 'Nombre', 'Estado'], ';');

            foreach ($huellas as $huella) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($huella['created_at'])),
                    $huella['accion'],
                    $huella['cedula'],
                    $huella['nombre'],
                    'REGISTRADA'
                ], ';');
            }
            fclose($output);
            exit();
        } else {
            require_once __DIR__ . '/../Views/reportes/exportar/huellas.php';
        }
    }

    /**
     * Exporta el reporte de postulados
     */
    private function exportarPostulados()
    {
        $filtros = [
            'tipomiembro' => $_GET['tipomiembro'] ?? '',
            'en_votacion' => $_GET['en_votacion'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? '',
            'cedula' => $_GET['cedula'] ?? ''
        ];

        $postulados = $this->reporte->getReportePostulados($filtros);
        $estadisticas = $this->reporte->getEstadisticasPostulados();

        $formato = $_GET['formato'] ?? 'print';

        if ($formato === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_postulados_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            fputcsv($output, ['REPORTE DE POSTULADOS'], ';');
            fputcsv($output, ['Total Postulados', $estadisticas['total_postulados']], ';');
            fputcsv($output, ['En Votación', $estadisticas['en_votacion']], ';');
            fputcsv($output, [], ';');

            fputcsv($output, ['Tipo Miembro', 'Cédula', 'Nombre', 'Profesión', 'Correo', 'Referencia', 'Votación'], ';');

            foreach ($postulados as $p) {
                fputcsv($output, [
                    $p['tipomiembro'],
                    ($p['nacionalidad_codigo'] ?? 'V') . '-' . $p['cedula'],
                    $p['nombre'],
                    $p['profesion'],
                    $p['correo_electronico'],
                    $p['tipo_referencia'] . ': ' . $p['referencia_id'],
                    $p['en_votacion'] ? 'SÍ' : 'NO'
                ], ';');
            }
            fclose($output);
            exit();
        } else {
            require_once __DIR__ . '/../Views/reportes/exportar/postulados.php';
        }
    }

    /**
     * Verifica que el usuario esté autenticado antes de permitir el acceso a los reportes.
     * Si no está autenticado, redirige al login.
     * 
     * @return void
     */
    private function checkAuth()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit();
        }
    }
}
