<?php

// Define project root
define('PROJECT_ROOT', dirname(__DIR__));

// Load bootstrap (handles autoloader, session, env, etc.)
require_once PROJECT_ROOT . '/src/bootstrap.php';

// Router Logic
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// Global Authentication Check
$public_actions = ['login', 'login_huella', 'get_fingerprint_templates', 'logout', 'register_socio', 'ia_query', 'forgot_password', 'reset_password_request', 'verify_reset_code', 'reset_password_form', 'reset_password_update', 'votaciones_remoto'];

if (!in_array($action, $public_actions)) {
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php?action=login");
        exit();
    }
    
    // Global Authorization Check for SOCIO
    // If the user is a SOCIO, restrict access to specific actions
    if (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO') {
        $allowed_socio_actions = [
            'logout', 
            'votaciones',
            'votaciones_votar', 
            'votaciones_resultados', 
            'votaciones_postulados',
            'votaciones_reporte_aceptados',
            'documentos',
            'documentos_download',
            'huella_verify',
            // Add other allowed actions for partners here
        ];
        
        if (!in_array($action, $allowed_socio_actions)) {
            // Redirect to dashboard if trying to access unauthorized area
            header("Location: dashboard.php?error=acceso_denegado");
            exit();
        }
    } else if (isset($_SESSION['tipousuario']) && strtolower($_SESSION['tipousuario']) !== 'administrador') {
        // Dynamic Authorization Check for other roles
        $database = new Database();
        $db = $database->getConnection();
        $permisoModel = new Permiso($db);
        
        $always_allowed = ['dashboard', 'logout', 'system_metrics', 'huella_verify', 'dashboard_stats'];
        if (!in_array($action, $always_allowed)) {
            if (!$permisoModel->hasAccess($_SESSION['tipousuario'], $action)) {
                // Return json if ajax, else redirect
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                    exit();
                }
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'No tiene permisos para ejecutar esta acción o acceder a este módulo.'];
                header("Location: dashboard.php");
                exit();
            }
        }
    }
}

switch ($action) {
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;
    case 'login_huella':
        $controller = new AuthController();
        $controller->loginHuella();
        break;
    case 'get_fingerprint_templates':
        $controller = new AuthController();
        $controller->getFingerprintTemplates();
        break;
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    case 'forgot_password':
        $controller = new AuthController();
        $controller->forgotPassword();
        break;
    case 'reset_password_request':
        $controller = new AuthController();
        $controller->resetPasswordRequest();
        break;
    case 'verify_reset_code':
        $controller = new AuthController();
        $controller->verifyResetCode();
        break;
    case 'reset_password_form':
        $controller = new AuthController();
        $controller->resetPasswordForm();
        break;
    case 'reset_password_update':
        $controller = new AuthController();
        $controller->resetPasswordUpdate();
        break;
    case 'socios':
        $controller = new SocioController();
        $controller->index();
        break;
    case 'socios_create':
        $controller = new SocioController();
        $controller->create();
        break;
    case 'socios_edit':
        $controller = new SocioController();
        $controller->edit();
        break;
    case 'socios_update':
        $controller = new SocioController();
        $controller->update();
        break;
    case 'socios_delete':
        $controller = new SocioController();
        $controller->delete();
        break;
    case 'socios_status':
        $controller = new SocioController();
        $controller->changeStatus();
        break;
    case 'socios_buscar_postulado':
        $controller = new SocioController();
        $controller->buscarPostulado();
        break;
    case 'usuarios':
        $controller = new UsuarioController();
        $controller->index();
        break;
    case 'usuarios_create':
        $controller = new UsuarioController();
        $controller->create();
        break;
    case 'usuarios_edit':
        $controller = new UsuarioController();
        $controller->edit();
        break;
    case 'usuarios_update':
        $controller = new UsuarioController();
        $controller->update();
        break;
    case 'usuarios_change_password':
        $controller = new UsuarioController();
        $controller->changePassword();
        break;
    case 'usuarios_delete':
        $controller = new UsuarioController();
        $controller->delete();
        break;
    case 'usuarios_toggle_block':
        $controller = new UsuarioController();
        $controller->toggleBlock();
        break;
    case 'puestos_trabajo':
        $controller = new PuestoTrabajoController();
        $controller->index();
        break;
    case 'puestos_trabajo_create':
        $controller = new PuestoTrabajoController();
        $controller->create();
        break;
    case 'puestos_trabajo_update':
        $controller = new PuestoTrabajoController();
        $controller->update();
        break;
    case 'puestos_trabajo_delete':
        $controller = new PuestoTrabajoController();
        $controller->delete();
        break;
    case 'conyugues':
        $controller = new ConyugueController();
        $controller->index();
        break;
    case 'conyugues_create':
        $controller = new ConyugueController();
        $controller->create();
        break;
    case 'conyugues_edit':
        $controller = new ConyugueController();
        $controller->edit();
        break;
    case 'conyugues_update':
        $controller = new ConyugueController();
        $controller->update();
        break;
    case 'conyugues_delete':
        $controller = new ConyugueController();
        $controller->delete();
        break;
    case 'votaciones':
        $controller = new VotacionController();
        $controller->index();
        break;
    case 'votaciones_configuracion':
        $controller = new VotacionController();
        $controller->configuracion();
        break;
    case 'votaciones_votar':
        $controller = new VotacionController();
        $controller->votar();
        break;
    case 'votaciones_resultados':
        $controller = new VotacionController();
        $controller->resultados();
        break;
    case 'votaciones_postulados':
        $controller = new VotacionController();
        $controller->postulados();
        break;
    case 'votaciones_crear_postulado':
        $controller = new VotacionController();
        $controller->crearPostulado();
        break;
    case 'votaciones_eliminar_postulado':
        $controller = new VotacionController();
        $controller->eliminarPostulado();
        break;
    case 'votaciones_reporte_aceptados':
        $controller = new VotacionController();
        $controller->reporteAceptados();
        break;
    case 'votaciones_finalizar':
        $controller = new VotacionController();
        $controller->finalizarVotacion();
        break;
    case 'votaciones_historico':
        $controller = new VotacionController();
        $controller->historico();
        break;
    case 'votaciones_tokens':
        $controller = new VotacionController();
        $controller->tokens();
        break;
    case 'votaciones_remoto_generate':
        $controller = new VotacionController();
        $controller->generarToken();
        break;
    case 'votaciones_remoto':
        $controller = new VotacionController();
        $controller->votarRemoto();
        break;
    // Rutas de Postulados (acceso independiente desde Gestión Operativa)
    case 'postulados':
        $controller = new PostuladoController();
        $controller->index();
        break;
    case 'postulados_create':
        $controller = new PostuladoController();
        $controller->create();
        break;
    case 'postulados_edit':
        $controller = new PostuladoController();
        $controller->edit();
        break;
    case 'postulados_delete':
        $controller = new PostuladoController();
        $controller->delete();
        break;
    case 'huella_save':
        $controller = new HuellaController();
        $controller->save();
        break;
    case 'huella_verify':
        $controller = new HuellaController();
        $controller->verify();
        break;
    case 'huella_get_all':
        $controller = new HuellaController();
        $controller->getAll();
        break;
    case 'huella_delete':
        $controller = new HuellaController();
        $controller->delete();
        break;
    // Rutas de Auditoría
    case 'auditoria':
        $controller = new AuditoriaController();
        $controller->index();
        break;
    case 'auditoria_limpiar':
        $controller = new AuditoriaController();
        $controller->limpiar();
        break;
    case 'auditoria_exportar':
        $controller = new AuditoriaController();
        $controller->exportar();
        break;
    case 'tipos_usuario':
        $controller = new TipoUsuarioController();
        $controller->index();
        break;
    case 'tipos_usuario_create':
        $controller = new TipoUsuarioController();
        $controller->create();
        break;
    case 'tipos_usuario_update':
        $controller = new TipoUsuarioController();
        $controller->update();
        break;
    case 'tipos_usuario_delete':
        $controller = new TipoUsuarioController();
        $controller->delete();
        break;
    // Rutas de Reportes
    case 'reportes':
        $controller = new ReporteController();
        $controller->index();
        break;
    case 'reportes_socios':
        $controller = new ReporteController();
        $controller->socios();
        break;
    case 'reportes_votaciones':
        $controller = new ReporteController();
        $controller->votaciones();
        break;
    case 'reportes_conyugues':
        $controller = new ReporteController();
        $controller->conyugues();
        break;
    case 'reportes_usuarios':
        $controller = new ReporteController();
        $controller->usuarios();
        break;
    case 'reportes_huellas':
        $controller = new ReporteController();
        $controller->huellas();
        break;
    case 'reportes_postulados':
        $controller = new ReporteController();
        $controller->postulados();
        break;
    // Rutas de Inteligencia Artificial
    case 'ia_dashboard':
        $controller = new IAController();
        $controller->index();
        break;
    case 'ia_query':
        $controller = new IAController();
        $controller->procesarConsulta();
        break;
    case 'ia_entrenamiento':
        $controller = new IAController();
        $controller->entrenamiento();
        break;
    case 'reportes_exportar':
        $controller = new ReporteController();
        $controller->exportar();
        break;
    // Rutas de Configuración
    case 'configuracion':
        $controller = new ConfiguracionController();
        $controller->index();
        break;
    case 'configuracion_update':
        $controller = new ConfiguracionController();
        $controller->update();
        break;
    case 'configuracion_create':
        $controller = new ConfiguracionController();
        $controller->create();
        break;
    case 'configuracion_delete':
        $controller = new ConfiguracionController();
        $controller->delete();
        break;
    case 'configuracion_reset':
        $controller = new ConfiguracionController();
        $controller->reset();
        break;
    case 'puente':
        require_once PROJECT_ROOT . '/src/Controllers/PuenteController.php';
        $controller = new PuenteController();
        $controller->index();
        break;
    case 'puente_start':
        require_once PROJECT_ROOT . '/src/Controllers/PuenteController.php';
        $controller = new PuenteController();
        $controller->start();
        break;
    case 'puente_stop':
        require_once PROJECT_ROOT . '/src/Controllers/PuenteController.php';
        $controller = new PuenteController();
        $controller->stop();
        break;
    case 'puente_clear_logs':
        require_once PROJECT_ROOT . '/src/Controllers/PuenteController.php';
        $controller = new PuenteController();
        $controller->clearLogs();
        break;
    case 'permisos':
    case 'permisos_update':
        $controller = new PermisoController();
        $controller->index();
        break;
    case 'register_socio':
        $controller = new AuthController();
        $controller->registerSocio();
        break;
    // Rutas de Profesiones (Maestros)
    case 'profesiones':
        $controller = new ProfesionController();
        $controller->index();
        break;
    case 'profesiones_create':
        $controller = new ProfesionController();
        $controller->create();
        break;
    case 'profesiones_edit':
        $controller = new ProfesionController();
        $controller->edit();
        break;
    case 'profesiones_delete':
        $controller = new ProfesionController();
        $controller->delete();
        break;
    // Rutas de Parentescos (Maestros)
    case 'tipos_relacion':
        $controller = new TipoRelacionController();
        $controller->index();
        break;
    case 'tipos_relacion_create':
        $controller = new TipoRelacionController();
        $controller->create();
        break;
    case 'tipos_relacion_edit':
        $controller = new TipoRelacionController();
        $controller->edit();
        break;
    case 'tipos_relacion_delete':
        $controller = new TipoRelacionController();
        $controller->delete();
        break;
    // Rutas de Estado Civil (Maestros)
    case 'estado_civil':
        $controller = new EstadoCivilController();
        $controller->index();
        break;
    case 'estado_civil_create':
        $controller = new EstadoCivilController();
        $controller->create();
        break;
    case 'estado_civil_edit':
        $controller = new EstadoCivilController();
        $controller->edit();
        break;
    case 'estado_civil_delete':
        $controller = new EstadoCivilController();
        $controller->delete();
        break;
    // Rutas de Nacionalidades (Maestros)
    case 'nacionalidades':
        $controller = new NacionalidadController();
        $controller->index();
        break;
    case 'nacionalidades_create':
        $controller = new NacionalidadController();
        $controller->create();
        break;
    case 'nacionalidades_edit':
        $controller = new NacionalidadController();
        $controller->edit();
        break;
    case 'nacionalidades_delete':
        $controller = new NacionalidadController();
        $controller->delete();
        break;
    // Rutas de Documentos
    case 'documentos':
        $controller = new DocumentoController();
        $controller->index();
        break;
    case 'documentos_download':
        $controller = new DocumentoController();
        $controller->download();
        break;

    // Rutas de Empresas
    case 'empresas':
        $controller = new EmpresaController();
        $controller->index();
        break;
    case 'empresas_create':
        $controller = new EmpresaController();
        $controller->create();
        break;
    case 'empresas_edit':
        $controller = new EmpresaController();
        $controller->edit();
        break;

    case 'empresas_delete':
        $controller = new EmpresaController();
        $controller->delete();
        break;
    // Rutas de Respaldo de Base de Datos
    case 'backup':
        $controller = new AdminController();
        $controller->backupIndex();
        break;
    case 'backup_create':
        $controller = new AdminController();
        $controller->backup();
        break;
    case 'backup_list':
        $controller = new AdminController();
        $controller->backupList();
        break;
    case 'backup_download':
        $controller = new AdminController();
        $controller->backupDownload();
        break;
    case 'backup_delete':
        $controller = new AdminController();
        $controller->backupDelete();
        break;
    case 'system_metrics':
        $controller = new AdminController();
        $controller->getMetrics();
        break;
    case 'dashboard_stats':
        $controller = new AdminController();
        $controller->getDashboardStats();
        break;
    default:
        // Default to login for now
        $auth = new AuthController();
        $auth->login();
        break;
}
?>
