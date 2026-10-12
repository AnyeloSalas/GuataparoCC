<?php
// Ensure session is started if using session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$tipo = $_SESSION['tipousuario'] ?? '';

// Obtener permisos dinámicos
$permisos_usuario_sidebar = [];
if ($tipo && strtoupper($tipo) !== 'SOCIO' && strtolower($tipo) !== 'administrador') {
    require_once __DIR__ . '/../../../config/Database.php';
    require_once __DIR__ . '/../../Models/Permiso.php';
    $db_conn = (new Database())->getConnection();
    $stmt = $db_conn->prepare("SELECT m.nombre FROM permisos_tipousuario p JOIN modulos m ON p.modulo_id = m.id WHERE LOWER(p.tipousuario) = LOWER(:tipousuario)");
    $stmt->execute([':tipousuario' => $tipo]);
    $permisos_usuario_sidebar = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function sidebar_has_access($modulo, $tipo, $permisos_usuario_sidebar) {
    if (strtoupper($tipo) === 'SOCIO') return false;
    if (strtolower($tipo) === 'administrador') return true;
    return in_array($modulo, $permisos_usuario_sidebar);
}
?>
<style>
    /* Premium Light Sidebar Design */
    #accordionSidebar {
        background: #ffffff !important;
        border-right: 1px solid #e3e6f0 !important;
        box-shadow: 4px 0 10px rgba(0,0,0,0.03) !important;
        z-index: 100;
        transition: width 0.3s ease;
    }

    .sidebar-brand {
        padding: 2.5rem 0 !important;
        background: #ffffff !important;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #f8f9fc;
    }

    #accordionSidebar .nav-item .nav-link {
        transition: all 0.25s ease-in-out;
        border-radius: 12px;
        margin: 4px 14px;
        padding: 12px 18px;
        color: #4a5568 !important; /* Dark Gray for text */
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        width: auto;
        position: relative;
    }
    
    #accordionSidebar .nav-item .nav-link i {
        font-size: 1.1rem;
        width: 1.8rem;
        margin-right: 0.5rem;
        text-align: center;
        color: #718096 !important; /* Muted slate icons */
        transition: all 0.2s ease;
    }

    #accordionSidebar .nav-item .nav-link:hover {
        background-color: #f7fafc !important;
        color: var(--primary-color) !important;
    }
    
    #accordionSidebar .nav-item .nav-link:hover i {
        color: var(--primary-color) !important;
        transform: scale(1.1);
    }
    
    #accordionSidebar .nav-item.active .nav-link {
        background-color: rgba(0, 26, 114, 0.08) !important;
        color: var(--primary-color) !important;
        font-weight: 800;
    }

    #accordionSidebar .nav-item.active .nav-link::before {
        content: "";
        position: absolute;
        left: -14px;
        top: 20%;
        height: 60%;
        width: 5px;
        background: var(--primary-color);
        border-radius: 0 4px 4px 0;
    }
    
    #accordionSidebar .nav-item.active .nav-link i {
        color: var(--primary-color) !important;
        opacity: 1;
    }

    .sidebar-heading {
        margin-top: 1.8rem;
        margin-bottom: 0.5rem;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: #a0aec0 !important;
        padding-left: 1.8rem;
    }
    
    .sidebar-divider {
        margin: 1.2rem 1.2rem !important;
        border-top: 1px solid #edf2f7;
    }

    /* Submenu styling - Light & Clean */
    .collapse-inner {
        background-color: #f8fafc !important;
        margin: 4px 14px 12px 14px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .collapse-item {
        color: #718096 !important;
        padding: 10px 18px !important;
        font-size: 0.85rem !important;
        font-weight: 600;
        transition: all 0.2s ease;
        border-radius: 8px;
        margin: 3px 10px;
    }
    
    .collapse-item:hover {
        background-color: #edf2f7 !important;
        color: var(--primary-color) !important;
        padding-left: 22px !important;
    }
    
    .collapse-item.active {
        color: var(--primary-color) !important;
        background-color: #ffffff !important;
        font-weight: 800;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04);
    }

    /* Fix for collapsed state */
    .sidebar.toggled .nav-item .nav-link span {
        display: none !important;
    }
    .sidebar.toggled .sidebar-heading {
        display: none !important;
    }
    .sidebar.toggled .nav-item .nav-link {
        justify-content: center;
        padding: 14px 0;
        margin: 4px 8px;
    }
    .sidebar.toggled .nav-item .nav-link i {
        margin-right: 0;
        width: 100%;
    }
    .sidebar.toggled .nav-item.active .nav-link::before {
        left: -8px;
    }
</style>

<!-- Sidebar -->
<ul class="navbar-nav bg-white sidebar sidebar-light accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center mb-3" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <img src="assets/img/logo_guataparo2.png" alt="Guataparo" style="max-height: 55px; width: auto; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.4));">
        </div>
    </a>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo (!isset($_GET['action']) || $_GET['action'] == 'dashboard') ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php" title="Inicio">
            <i class="fas fa-fw fa-home"></i>
            <span>Inicio</span></a>
    </li>



    <!-- Divider -->
    <hr class="sidebar-divider">

    <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
    
    <!-- Heading - Gestión -->
    <div class="sidebar-heading">
        Gestión Operativa
    </div>

    <!-- Nav Item - Socios -->
    <?php if (sidebar_has_access('socios', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'socios') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=socios" title="Gestión de Socios">
            <i class="fas fa-fw fa-users"></i>
            <span>Socios</span></a>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Familiares -->
    <?php if (sidebar_has_access('conyugues', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'conyugues') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=conyugues" title="Gestión de Familiares">
            <i class="fas fa-fw fa-user-friends"></i>
            <span>Familiares</span></a>
    </li>
    <?php endif; ?>
    
    <!-- Nav Item - Votaciones (Movido aquí por ser operativo para administradores) -->
    <?php if (sidebar_has_access('votaciones', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'votaciones') !== false && strpos($_GET['action'], 'postulados') === false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=votaciones" title="Votaciones">
            <i class="fas fa-fw fa-check-square"></i>
            <span>Votaciones</span></a>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Postulados -->
    <?php if (sidebar_has_access('postulados', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'postulados') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=postulados" title="Postulados">
            <i class="fas fa-fw fa-user-tie"></i>
            <span>Postulados</span></a>
    </li>
    <?php endif; ?>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Administración -->
    <div class="sidebar-heading">
        Administración
    </div>

    <!-- Nav Item - Maestros Collapse Menu -->
    <?php
    $has_any_maestro = sidebar_has_access('profesiones', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('empresas', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('tipos_relacion', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('estado_civil', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('nacionalidades', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('puestos_trabajo', $tipo, $permisos_usuario_sidebar) ||
                       sidebar_has_access('tipos_usuario', $tipo, $permisos_usuario_sidebar);
    if ($has_any_maestro): 
    ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'profesiones') !== false || strpos($_GET['action'], 'empresas') !== false || strpos($_GET['action'], 'tipos_relacion') !== false || strpos($_GET['action'], 'estado_civil') !== false || strpos($_GET['action'], 'nacionalidades') !== false || strpos($_GET['action'], 'puestos_trabajo') !== false || strpos($_GET['action'], 'tipos_usuario') !== false)) ? 'active' : ''; ?>">
        <a class="nav-link <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'profesiones') !== false || strpos($_GET['action'], 'empresas') !== false || strpos($_GET['action'], 'tipos_relacion') !== false || strpos($_GET['action'], 'estado_civil') !== false || strpos($_GET['action'], 'nacionalidades') !== false || strpos($_GET['action'], 'puestos_trabajo') !== false || strpos($_GET['action'], 'tipos_usuario') !== false)) ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseMaestros"
            aria-expanded="true" aria-controls="collapseMaestros" title="Tablas Maestras">
            <i class="fas fa-fw fa-layer-group"></i>
            <span>Maestros</span>
        </a>
        <div id="collapseMaestros" class="collapse <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'profesiones') !== false || strpos($_GET['action'], 'empresas') !== false || strpos($_GET['action'], 'tipos_relacion') !== false || strpos($_GET['action'], 'estado_civil') !== false || strpos($_GET['action'], 'nacionalidades') !== false || strpos($_GET['action'], 'puestos_trabajo') !== false || strpos($_GET['action'], 'tipos_usuario') !== false)) ? 'show' : ''; ?>" aria-labelledby="headingMaestros" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Tablas Maestras:</h6>
                <?php if (sidebar_has_access('profesiones', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'profesiones') !== false) ? 'active' : ''; ?>" href="index.php?action=profesiones">
                    <i class="fas fa-briefcase mr-2"></i>Profesiones
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('empresas', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'empresas') !== false) ? 'active' : ''; ?>" href="index.php?action=empresas">
                    <i class="fas fa-building mr-2"></i>Empresas
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('tipos_relacion', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'tipos_relacion') !== false) ? 'active' : ''; ?>" href="index.php?action=tipos_relacion">
                    <i class="fas fa-users-cog mr-2"></i>Parentescos
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('estado_civil', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'estado_civil') !== false) ? 'active' : ''; ?>" href="index.php?action=estado_civil">
                    <i class="fas fa-heart mr-2"></i>Estado Civil
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('nacionalidades', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'nacionalidades') !== false) ? 'active' : ''; ?>" href="index.php?action=nacionalidades">
                    <i class="fas fa-globe mr-2"></i>Nacionalidades
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('puestos_trabajo', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'puestos_trabajo') !== false) ? 'active' : ''; ?>" href="index.php?action=puestos_trabajo">
                    <i class="fas fa-id-badge mr-2"></i>Puestos Trab.
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('tipos_usuario', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'tipos_usuario') !== false) ? 'active' : ''; ?>" href="index.php?action=tipos_usuario">
                    <i class="fas fa-user-tag mr-2"></i>Tipos de Usuario
                </a>
                <?php endif; ?>
            </div>
        </div>
    </li>
    <?php endif; ?>



    <!-- Nav Item - Usuarios Collapse Menu -->
    <?php if (sidebar_has_access('usuarios', $tipo, $permisos_usuario_sidebar) || sidebar_has_access('permisos', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'usuarios') !== false || strpos($_GET['action'], 'permisos') !== false)) ? 'active' : ''; ?>">
        <a class="nav-link <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'usuarios') !== false || strpos($_GET['action'], 'permisos') !== false)) ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseUsuarios"
            aria-expanded="true" aria-controls="collapseUsuarios" title="Seguridad y Accesos">
            <i class="fas fa-fw fa-users-cog"></i>
            <span>Seguridad y Accesos</span>
        </a>
        <div id="collapseUsuarios" class="collapse <?php echo (isset($_GET['action']) && (strpos($_GET['action'], 'usuarios') !== false || strpos($_GET['action'], 'permisos') !== false)) ? 'show' : ''; ?>" aria-labelledby="headingUsuarios" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Gestión de Accesos:</h6>
                <?php if (sidebar_has_access('usuarios', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'usuarios') !== false) ? 'active' : ''; ?>" href="index.php?action=usuarios">
                    <i class="fas fa-id-card mr-2"></i>Usuarios Sistema
                </a>
                <?php endif; ?>
                <?php if (sidebar_has_access('permisos', $tipo, $permisos_usuario_sidebar)): ?>
                <a class="collapse-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'permisos') !== false) ? 'active' : ''; ?>" href="index.php?action=permisos">
                    <i class="fas fa-user-lock mr-2"></i>Roles y Permisos
                </a>
                <?php endif; ?>
            </div>
        </div>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Guataparo AI -->
    <?php if (sidebar_has_access('ia_dashboard', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'ia_dashboard') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=ia_dashboard" title="Guataparo AI">
            <i class="fas fa-fw fa-robot"></i>
            <span>Guataparo AI</span></a>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Reportes -->
    <?php if (sidebar_has_access('reportes', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'reportes') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=reportes" title="Reportes e Inteligencia">
            <i class="fas fa-fw fa-chart-pie"></i>
            <span>Reportes e Inteligencia</span></a>
    </li>
    <?php endif; ?>
    
    <!-- Nav Item - Auditoria -->
    <?php if (sidebar_has_access('auditoria', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'auditoria') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=auditoria" title="Auditoría de Seguridad">
            <i class="fas fa-fw fa-shield-alt"></i>
            <span>Auditoría de Seguridad</span></a>
    </li>
    <?php endif; ?>
    
    <!-- Nav Item - Configuración -->
    <?php if (sidebar_has_access('configuracion', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'configuracion') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=configuracion" title="Configuración Global">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Configuración Global</span></a>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Puente Biométrico -->
    <?php if (strtolower($tipo) === 'administrador'): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'puente') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=puente" title="Puente Biométrico">
            <i class="fas fa-fw fa-fingerprint"></i>
            <span>Puente Biométrico</span></a>
    </li>
    <?php endif; ?>

    <!-- Nav Item - Respaldo BD -->
    <?php if (sidebar_has_access('backup', $tipo, $permisos_usuario_sidebar)): ?>
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'backup') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=backup" title="Respaldo BD">
            <i class="fas fa-fw fa-database"></i>
            <span>Respaldo BD</span></a>
    </li>
    <?php endif; ?>
    


    <?php else: ?>
    
    <!-- Vista simplificada para Socios -->
    <div class="sidebar-heading">
        Mi Cuenta
    </div>
    
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'votaciones') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=votaciones" title="Participar en Votación">
            <i class="fas fa-fw fa-vote-yea"></i>
            <span>Participar en Votación</span></a>
    </li>
    
    <li class="nav-item <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'documentos') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php?action=documentos" title="Requisitos y Documentos">
            <i class="fas fa-fw fa-file-download"></i>
            <span>Requisitos y Documentos</span></a>
    </li>
    
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
