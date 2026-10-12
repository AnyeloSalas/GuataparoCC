<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Controlador de Votaciones (VotacionController)
 * 
 * Gestiona todo el ciclo de vida del proceso electoral: configuración de fechas,
 * registro de postulados, emisión de votos (presenciales y remotos),
 * visualización de resultados y archivado del histórico.
 */
class VotacionController
{
    /** @var PDO|null Conexión a la base de datos */
    private $db;
    /** @var Votacion Instancia del modelo Votacion */
    private $votacion;
    /** @var Postulado Instancia del modelo Postulado */
    private $postulado;
    /** @var Socio Instancia del modelo Socio */
    private $socio;
    /** @var Huella Instancia del modelo Huella */
    private $huella;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos y los modelos necesarios.
     */
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->votacion = new Votacion($this->db);
        $this->postulado = new Postulado($this->db);
        $this->socio = new Socio($this->db);
        $this->huella = new Huella($this->db);
    }

    /**
     * Muestra el dashboard principal del módulo de votaciones.
     * Presenta estadísticas generales y el estado del periodo actual.
     * 
     * @return void
     */
    public function index()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;

        $fechas = $this->votacion->getFechas();
        $enPeriodo = $this->votacion->enPeriodoVotacion('votacion');
        $stats = $this->votacion->getEstadisticas();

        // Obtener postulados seleccionados para votación
        $stmt = $this->postulado->readEnVotacion();
        $postulados_en_votacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
        $configModel = new Configuracion($this->db);
        $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

        require_once PROJECT_ROOT . '/src/Views/votaciones/index.php';
    }

    /**
     * Muestra y procesa la configuración del proceso de votación.
     * Permite establecer fechas, quórum, porcentajes de aceptación y limpiar votos.
     * Solo accesible para Administradores.
     * 
     * @return void
     */
    public function configuracion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                $error = "Token de seguridad inválido o expirado. Por favor intente nuevamente.";
            }

            if (!isset($error)) {
                if (isset($_POST['accion_config']) && $_POST['accion_config'] == 'limpiar_votos') {
                    if ($this->votacion->limpiarVotosPeriodo()) {
                        $success = "Se han eliminado todos los votos del periodo actual correctamente.";
                    } else {
                        $error = "Error al intentar eliminar los votos.";
                    }
                } else {
                    $this->votacion->iniciov = $_POST['iniciov'];
                    $this->votacion->finv = $_POST['finv'];

                    require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
                    $configModel = new Configuracion($this->db);
                    $porcentaje = isset($_POST['porcentaje_aceptacion']) ? (int) $_POST['porcentaje_aceptacion'] : 50;
                    $minimo_votantes = isset($_POST['minimo_votantes']) ? (int) $_POST['minimo_votantes'] : 0;

                    if ($configModel->getFull('votacion_porcentaje_aceptacion')) {
                        $configModel->update('votacion_porcentaje_aceptacion', $porcentaje);
                    } else {
                        $configModel->create('votacion_porcentaje_aceptacion', $porcentaje, 'Porcentaje de votos necesarios para aceptar a un socio', 'number', 'votacion');
                    }

                    if ($configModel->getFull('votacion_minimo_votantes')) {
                        $configModel->update('votacion_minimo_votantes', $minimo_votantes);
                    } else {
                        $configModel->create('votacion_minimo_votantes', $minimo_votantes, 'Mínimo de votantes (quórum) para que el proceso sea válido', 'number', 'votacion');
                    }

                    if ($this->votacion->actualizarFechas()) {
                        // Actualizar postulados seleccionados para votación
                        $seleccionados = isset($_POST['postulados_seleccionados']) ? $_POST['postulados_seleccionados'] : [];
                        $this->postulado->updateEnVotacion($seleccionados);

                        $success = "Configuración guardada correctamente.";
                    } else {
                        $error = "Error al actualizar configuración.";
                    }
                }
            }
        }

        require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
        $configModel = new Configuracion($this->db);
        $porcentaje_aceptacion = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
        $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

        $fechas = $this->votacion->getFechas();

        // Obtener postulados para mostrar en la vista
        $stmt = $this->postulado->read();
        $postulados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Views/votaciones/configuracion.php';
    }

    /**
     * Muestra la interfaz para emitir un voto presencial y procesa la selección.
     * Valida que el usuario esté en periodo de votación y que no haya votado previamente.
     * 
     * @return void
     */
    public function votar()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        // Verificar período de votación
        if (!$this->votacion->enPeriodoVotacion('votacion')) {
            header("Location: index.php?action=votaciones&error=fuera_periodo");
            exit();
        }

        // Si es un socio logueado (por huella o normal), filtrar sus acciones
        $es_socio = ($_SESSION['tipousuario'] == 'SOCIO');
        $socio_detalle = null;

        if ($es_socio) {
            $acciones = $this->socio->getAccionesPorSocio($_SESSION['usuario']);
            if (!empty($acciones)) {
                $socio_detalle = $acciones[0]['nombre'];
            }
        } else {
            $acciones = $this->socio->getAccionesActivas();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                $error = "Token de seguridad inválido o expirado. Por favor intente nuevamente.";
            }

            if (!isset($error)) {
                $accion = trim($_POST['accion'] ?? '');
                $votos = isset($_POST['votos']) ? $_POST['votos'] : [];

                // Validar que se haya seleccionado acción y votos
                if (empty($accion) || empty($votos)) {
                    $error = "Debe seleccionar su acción y votar por todos los candidatos.";
                } elseif ($es_socio && !in_array($accion, array_column($acciones, 'accion'))) {
                    // Control IDOR: El socio solo puede emitir votos para sus propias acciones activas
                    $error = "Acceso denegado: La acción seleccionada (" . htmlspecialchars($accion) . ") no pertenece a su cuenta.";
                } elseif (!$es_socio && !in_array($accion, array_column($acciones, 'accion'))) {
                    // Operador/Admin: La acción debe pertenecer al censo de acciones activas
                    $error = "La acción seleccionada (" . htmlspecialchars($accion) . ") no es válida o no está activa en el censo.";
                } else {
                    // Verificar si ya votó
                    if ($this->votacion->yaVoto($accion)) {
                        $error = "Esta acción ya ha registrado sus votos.";
                    } else {
                        // Registrar cada voto dentro de una transacción ACID
                        $error_count = 0;
                        $resumen_votos = [];
                        $fecha_voto = date('Y-m-d H:i:s');

                        try {
                            $this->db->beginTransaction();

                            foreach ($votos as $cedula_candidato => $respuesta) {
                                $this->votacion->voto = 1;
                                $this->votacion->accion = $accion;
                                $this->votacion->cedula = $cedula_candidato;
                                $this->votacion->tipovoto = $respuesta;
                                $this->votacion->fecha = $fecha_voto;

                                if ($this->votacion->registrarVoto()) {
                                    // Obtener nombre del candidato para el resumen
                                    $cand_stmt = $this->db->prepare("SELECT CONCAT_WS(' ', primer_nombre, primer_apellido) as nombre FROM postulados WHERE cedula = ?");
                                    $cand_stmt->execute([$cedula_candidato]);
                                    $cand = $cand_stmt->fetch(PDO::FETCH_ASSOC);

                                    $resumen_votos[] = [
                                        'candidato' => $cand['nombre'] ?? 'Candidato ' . $cedula_candidato,
                                        'voto' => $respuesta
                                    ];
                                } else {
                                    $error_count++;
                                }
                            }

                            if ($error_count == 0) {
                                $this->db->commit();

                                // Guardar resumen en sesión para mostrarlo en el index
                                $_SESSION['ultimo_voto'] = [
                                    'accion' => $accion,
                                    'fecha' => date('d/m/Y H:i:s'),
                                    'votos' => $resumen_votos,
                                    'socio' => $socio_detalle ?? 'Socio Acción ' . $accion
                                ];

                                header("Location: index.php?action=votaciones_votar&success=1");
                                exit();
                            } else {
                                if ($this->db->inTransaction()) {
                                    $this->db->rollBack();
                                }
                                $error = "Hubo un error al registrar algunos votos. La boleta fue descartada.";
                            }
                        } catch (\Exception $e) {
                            if ($this->db->inTransaction()) {
                                $this->db->rollBack();
                            }
                            $error = "Error al procesar la votación: " . $e->getMessage();
                        }
                    }
                }
            }
        }

        // Obtener solo postulados en votación
        $stmt = $this->postulado->readEnVotacion();
        $postulados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener configuración de huella para la verificación
        require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
        $configModel = new Configuracion($this->db);
        $huellaConfig = [
            'driver' => $configModel->get('huella_driver') ?: 'futronic',
            'hik_ip' => $configModel->get('huella_hik_ip') ?: '127.0.0.1',
            'hik_port' => $configModel->get('huella_hik_port') ?: '8000',
            'hik_user' => $configModel->get('huella_hik_user') ?: 'admin',
            'hik_pass' => $configModel->get('huella_hik_pass') ?: '12345'
        ];

        require_once PROJECT_ROOT . '/src/Views/votaciones/votar.php';
    }

    /**
     * Muestra los resultados en tiempo real del proceso de votación actual.
     * Solo accesible para administradores (los socios no pueden ver resultados en curso).
     * 
     * @return void
     */
    public function resultados()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        // Los socios no pueden ver resultados
        if (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        $stats = $this->votacion->getEstadisticas();
        $stmt = $this->votacion->getVotosPorCandidato(null, true);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
        $configModel = new Configuracion($this->db);
        $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;

        require_once PROJECT_ROOT . '/src/Views/votaciones/resultados.php';
    }

    /**
     * Muestra el listado paginado de postulados a la junta directiva.
     * 
     * @return void
     */
    public function postulados()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $por_pagina = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($pagina < 1)
            $pagina = 1;

        $total_registros = $this->postulado->count();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        $stmt = $this->postulado->read($por_pagina, $offset);
        $postulados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once PROJECT_ROOT . '/src/Views/votaciones/postulados.php';
    }

    /**
     * Muestra el formulario y procesa la creación de un nuevo postulado.
     * Solo accesible para Administradores.
     * 
     * @return void
     */
    public function crearPostulado()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones_postulados");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                $error = "Token de seguridad inválido o expirado.";
            }

            if (!isset($error)) {
                $this->postulado->tipomiembro = $_POST['tipomiembro'];
                $this->postulado->cedula = $_POST['cedula'];
                $this->postulado->nombre = $_POST['nombre'];
                $this->postulado->accion = $_POST['accion'];
                $this->postulado->correo_electronico = $_POST['correo_electronico'];
                $this->postulado->profesion = $_POST['profesion'];
                $this->postulado->pos1 = $_POST['pos1'];
                $this->postulado->pos2 = $_POST['pos2'];
                $this->postulado->pos3 = $_POST['pos3'];
                $this->postulado->pos4 = $_POST['pos4'];
                $this->postulado->nacionalidad = $_POST['nacionalidad'];

                if ($this->postulado->create()) {
                    header("Location: index.php?action=votaciones_postulados");
                    exit();
                } else {
                    $error = "Error al crear postulado.";
                }
            }
        }

        require_once PROJECT_ROOT . '/src/Views/votaciones/crear_postulado.php';
    }

    /**
     * Elimina un postulado específico mediante su cédula.
     * Solo accesible para Administradores.
     * 
     * @return void
     */
    public function eliminarPostulado()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones_postulados");
            exit();
        }

        if (!isPost()) {
            flash('error', 'Método no permitido.');
            header("Location: index.php?action=votaciones_postulados");
            exit();
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido.');
            header("Location: index.php?action=votaciones_postulados");
            exit();
        }

        if (isset($_POST['cedula'])) {
            $this->postulado->cedula = $_POST['cedula'];
            if ($this->postulado->delete()) {
                header("Location: index.php?action=votaciones_postulados");
                exit();
            }
        }

        header("Location: index.php?action=votaciones_postulados&error=delete_failed");
        exit();
    }

    /**
     * Muestra el reporte final de candidatos aceptados basándose en el porcentaje
     * requerido y el quórum mínimo establecido en la configuración.
     * 
     * @return void
     */
    public function reporteAceptados()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        // Los socios no pueden ver reportes de resultados
        if (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        $stmt = $this->votacion->getVotosPorCandidato(null, true);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = $this->votacion->getEstadisticas();

        require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
        $configModel = new Configuracion($this->db);
        $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
        $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

        $quorum_alcanzado = ($stats['votantes_unicos'] >= $minimo_votantes);

        // Filtrar solo los aceptados según el porcentaje configurado y el quórum
        $aceptados = array_filter($resultados, function ($r) use ($porcentaje_req, $quorum_alcanzado) {
            if (!$quorum_alcanzado)
                return false;
            $total = (int) $r['votos_totales'];
            $req = ceil(($total * $porcentaje_req) / 100);
            return (int) $r['aceptados'] >= $req && $total > 0;
        });

        $fechas = $this->votacion->getFechas();

        require_once PROJECT_ROOT . '/src/Views/votaciones/reporte_aceptados.php';
    }

    /**
     * Finaliza el proceso de votación actual, archiva los resultados en el histórico
     * y limpia los postulados en votación.
     * Solo accesible para Administradores.
     * 
     * @return void
     */
    public function finalizarVotacion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                header("Location: index.php?action=votaciones&error=invalid_token");
                exit();
            }

            // Verificar que existan postulados en votación antes de finalizar
            $stmt = $this->postulado->readEnVotacion();
            $postulados_en_votacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($postulados_en_votacion)) {
                header("Location: index.php?action=votaciones&error=sin_postulados");
                exit();
            }

            $nombre_periodo = $_POST['nombre_periodo'] ?? 'Periodo Finalizado ' . date('d-m-Y');

            // 1. Archivar resultados
            if ($this->votacion->archivarPeriodo($nombre_periodo)) {
                // 2. Limpiar postulados en votación
                $this->postulado->updateEnVotacion([]);

                header("Location: index.php?action=votaciones&success=finalizado");
                exit();
            } else {
                header("Location: index.php?action=votaciones&error=archive_failed");
                exit();
            }
        }

        // Si no es POST, mostrar confirmación (usualmente handled by modal, but safety check)
        header("Location: index.php?action=votaciones");
        exit();
    }

    /**
     * Muestra el histórico de procesos de votación finalizados y sus resultados detallados.
     * 
     * @return void
     */
    public function historico()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }

        // Los socios no pueden ver el histórico de votaciones
        if (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        $filtros = [
            'tipomiembro' => $_GET['tipomiembro'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];

        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;

        $periodos = $this->votacion->getHistoricoPeriodos($fecha_desde, $fecha_hasta);
        $detalles = null;
        $periodo_seleccionado = null;

        if (isset($_GET['periodo_id']) && !empty($_GET['periodo_id'])) {
            $periodo_id = $_GET['periodo_id'];
            $detalles = $this->votacion->getResultadosHistoricos($periodo_id, $filtros);

            // Buscar datos del periodo para el título
            foreach ($periodos as $p) {
                if ($p['id'] == $periodo_id) {
                    $periodo_seleccionado = $p;
                    break;
                }
            }
        }

        require_once PROJECT_ROOT . '/src/Views/votaciones/historico.php';
    }

    // --- METODOS PARA VOTO REMOTO ---

    /**
     * Muestra la interfaz de gestión de tokens para el voto remoto.
     * 
     * @return void
     */
    public function tokens()
    {
        if (session_status() == PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        $acciones = $this->socio->getAccionesActivas();
        require_once PROJECT_ROOT . '/src/Views/votaciones/tokens.php';
    }

    /**
     * Genera un token seguro de un solo uso para voto remoto y envía el enlace
     * por correo electrónico al socio correspondiente.
     * 
     * @return void
     */
    public function generarToken()
    {
        if (session_status() == PHP_SESSION_NONE)
            session_start();
        if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
            header("Location: index.php?action=votaciones");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Token de seguridad inválido o expirado.'];
                header("Location: index.php?action=votaciones_tokens");
                exit();
            }

            $accion = $_POST['accion'];
            $horas = isset($_POST['horas']) ? intval($_POST['horas']) : 24;

            $token = $this->votacion->generarTokenVotoRemoto($accion, $horas);

            if ($token) {
                // Determinar el URL base para el link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                $path = rtrim($path, '/');
                $link = $protocol . "://" . $host . $path . "/index.php?action=votaciones_remoto&token=" . $token;

                // Obtener datos del socio
                $this->socio->accion = $accion;
                $email = null;
                $nombre_socio = 'Socio';
                if ($this->socio->readOne()) {
                    $email = $this->socio->email;
                    $nombre_socio = $this->socio->nombre;
                }

                $msg = 'Token generado exitosamente. Copie el siguiente enlace para la acción ' . htmlspecialchars($accion) . ': <br><br><div class="p-3 bg-light border rounded"><a href="' . $link . '" target="_blank" class="fw-bold">' . $link . '</a></div>';

                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if ($this->enviarEmailToken($email, $nombre_socio, $link, $accion)) {
                        $msg .= '<div class="alert alert-success mt-3 mb-0"><i class="fas fa-paper-plane mr-2"></i> Adicionalmente, se envió un **correo automático** a <strong>' . htmlspecialchars($email) . '</strong> con el enlace de votación.</div>';
                    } else {
                        $msg .= '<div class="alert alert-warning mt-3 mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> No se pudo enviar el correo automático a ' . htmlspecialchars($email) . '. Por favor, cópielo y envíelo de forma manual.</div>';
                    }
                } else {
                    $msg .= '<div class="alert alert-info mt-3 mb-0"><i class="fas fa-info-circle mr-2"></i> El socio de esta acción no tiene correo electrónico registrado o es inválido en la plataforma. Recuerde enviarle el enlace manualmente por mensajería.</div>';
                }

                $_SESSION['flash'] = ['type' => 'success', 'msg' => $msg];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error al generar el token interno.'];
            }
        }
        header("Location: index.php?action=votaciones_tokens");
        exit();
    }

    /**
     * Envía un correo electrónico con el enlace de voto remoto al socio.
     * 
     * @param string $email Correo electrónico del destinatario.
     * @param string $nombre Nombre del socio.
     * @param string $link Enlace seguro de votación.
     * @param string $accion Número de acción.
     * @return bool True si el correo se envió correctamente, False en caso contrario.
     */
    private function enviarEmailToken($email, $nombre, $link, $accion)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');

            $encryption = env('MAIL_ENCRYPTION', 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = env('MAIL_PORT', 587);
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 10;
            $mail->SMTPConnectTimeout = 10;

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Sistema Guataparo'));
            $mail->addAddress($email, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Enlace de Voto Remoto - Accion ' . $accion . ' - ' . env('APP_NAME', 'Guataparo');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #2c3e50; text-align: center;'>Voto No Presencial</h2>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Se ha emitido un acceso seguro de un solo uso para que usted pueda emitir su voto remoto representando a la Acción <strong>{$accion}</strong> en el <strong>" . env('APP_NAME', 'Sistema Guataparo') . "</strong>.</p>
                    <p>Para abrir su boleta, simplemente haga clic en el siguiente botón:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' style='background-color: #1e3c72; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;'>Abrir boleta de votación</a>
                    </div>
                    <p style='color: #dc3545;'><strong>Importante:</strong> Este enlace es intransferible y su validación es efímera. Una vez enviadas las respuestas, el token se autodestruirá inmediatamente.</p>
                    <p>Si el botón anterior no funciona por bloqueos de su correo de click, puede copiar y pegar la siguiente dirección en su navegador:</p>
                    <p style='font-size: 11px; background:#f0f0f0; padding:10px; word-break: break-all;'>{$link}</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #7f8c8d; text-align: center;'>Este es un mensaje automático del módulo de elecciones, por favor no lo responda.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            $logFile = LOGS_PATH . '/mail_error.log';
            $errorMsg = date('[Y-m-d H:i:s] ') . "Error enviando correo de Voto Remoto a $email: {$mail->ErrorInfo} | Exception: {$e->getMessage()}\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
            return false;
        }
    }

    /**
     * Interfaz pública para emitir un voto remoto mediante un token válido.
     * Valida el token, registra los votos y marca el token como usado.
     * 
     * @return void
     */
    public function votarRemoto()
    {
        if (session_status() == PHP_SESSION_NONE)
            session_start();

        $token = isset($_POST['token']) ? $_POST['token'] : (isset($_GET['token']) ? $_GET['token'] : null);
        if (!$token) {
            die("<div style='font-family: sans-serif; padding: 2rem; color: #dc3545;'><strong>Error:</strong> Enlace de votación inválido o vacío.</div>");
        }

        $valido_data = $this->votacion->validarTokenVotoRemoto($token);
        if (!$valido_data['valido']) {
            die("<div style='font-family: sans-serif; padding: 2rem; color: #dc3545;'><strong>Enlace Inválido:</strong> " . htmlspecialchars($valido_data['error']) . "</div>");
        }

        $id_token = $valido_data['id_token'];
        $accion = $valido_data['accion'];

        // Verificar período de votación
        if (!$this->votacion->enPeriodoVotacion('votacion')) {
            die("<div style='font-family: sans-serif; padding: 2rem; color: #dc3545;'><strong>Error:</strong> No se encuentra en período de votación activo.</div>");
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                $error = "Token de seguridad inválido o expirado. Por favor, refresque la página.";
            }

            $votos = isset($_POST['votos']) ? $_POST['votos'] : [];

            if (!isset($error)) {
                if (empty($votos)) {
                    $error = "Debe votar por todos los candidatos.";
                } else {
                    try {
                        $this->db->beginTransaction();

                        // Re-validar y bloquear fila del token dentro de la transacción con FOR UPDATE
                        $token_lock = $this->votacion->validarTokenVotoRemoto($token, true);
                        if (!$token_lock['valido']) {
                            if ($this->db->inTransaction()) {
                                $this->db->rollBack();
                            }
                            die("<div style='font-family: sans-serif; padding: 2rem; color: #dc3545;'><strong>Enlace Inválido o Ya Utilizado:</strong> " . htmlspecialchars($token_lock['error']) . "</div>");
                        }

                        if ($this->votacion->yaVoto($accion)) {
                            $this->votacion->marcarTokenUsado($id_token);
                            $this->db->commit();
                            die("<div style='font-family: sans-serif; padding: 2rem; color: #dc3545;'><strong>Error:</strong> La acción <strong>" . htmlspecialchars($accion) . "</strong> ya ha registrado sus votos previamente.</div>");
                        }

                        $error_count = 0;
                        $fecha_voto = date('Y-m-d H:i:s');

                        foreach ($votos as $cedula_candidato => $respuesta) {
                            $this->votacion->voto = 1;
                            $this->votacion->accion = $accion;
                            $this->votacion->cedula = $cedula_candidato;
                            $this->votacion->tipovoto = $respuesta;
                            $this->votacion->fecha = $fecha_voto;
                            $this->votacion->es_presencial = 0; // VOTO REMOTO

                            if (!$this->votacion->registrarVoto()) {
                                $error_count++;
                            }
                        }

                        if ($error_count == 0) {
                            $this->votacion->marcarTokenUsado($id_token);
                            $this->db->commit();
                            die("<div style='font-family: sans-serif; padding: 2rem; background: #d4edda; color: #155724; border-radius: 8px; text-align: center;'><h2>¡Voto Exitoso!</h2><p>Su voto remoto para la acción <strong>" . htmlspecialchars($accion) . "</strong> ha sido registrado con éxito.</p></div>");
                        } else {
                            if ($this->db->inTransaction()) {
                                $this->db->rollBack();
                            }
                            $error = "Hubo un error al guardar en la base de datos algunos votos. Su voto no fue procesado.";
                        }
                    } catch (\Exception $e) {
                        if ($this->db->inTransaction()) {
                            $this->db->rollBack();
                        }
                        $error = "Error crítico durante la emisión del voto remoto: " . $e->getMessage();
                    }
                }
            }
        }

        // Obtener solo postulados en votación
        $stmt = $this->postulado->readEnVotacion();
        $postulados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once PROJECT_ROOT . '/src/Views/votaciones/votar_remoto.php';
    }
}
?>