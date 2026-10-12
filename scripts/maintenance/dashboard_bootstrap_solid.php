<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipousuario'];
$sistemaNombre = "Sistema Guataparo"; 

require_once __DIR__ . '/../src/Views/layouts/header.php';
require_once __DIR__ . '/../src/Views/layouts/sidebar.php';
require_once __DIR__ . '/../src/Views/layouts/topbar.php';
?>

<!-- Begin Page Content -->
<!-- No custom CSS styles allowed, only Bootstrap classes -->

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Panel de Control</h6>
            </div>
            <div class="card-body">
                <h4 class="small font-weight-bold">Bienvenido, <?php echo htmlspecialchars($usuario); ?></h4>
                <p class="mb-0">Seleccione una opción del menú o utilice los accesos directos a continuación.</p>
                
                <?php if(isset($_GET['error']) && $_GET['error'] == 'acceso_denegado'): ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        ACCESO DENEGADO: No tiene permisos para ingresar a esa sección.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
    
    <!-- Socios Card (Solid Primary) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Gestión</div>
                        <div class="h5 mb-0 font-weight-bold">Socios</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=socios">Ver Detalles</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Usuarios Card (Solid Success) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Seguridad</div>
                        <div class="h5 mb-0 font-weight-bold">Usuarios</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-id-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=usuarios">Administrar</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Cónyuges Card (Solid Info) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Familiares</div>
                        <div class="h5 mb-0 font-weight-bold">Cónyuges</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-heart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=conyugues">Gestionar</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>

    <!-- Votaciones Card (Solid Warning) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Elecciones</div>
                        <div class="h5 mb-0 font-weight-bold">Votaciones</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-vote-yea fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=votaciones">Ingresar</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <?php if (strtoupper($tipo) !== 'SOCIO'): ?>

    <!-- Reportes Card (Solid Danger) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-danger text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Estadísticas</div>
                        <div class="h5 mb-0 font-weight-bold">Reportes</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=reportes">Ver Datos</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Configuración Card (Solid Secondary) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-secondary text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Sistema</div>
                        <div class="h5 mb-0 font-weight-bold">Configuración</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cogs fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=configuracion">Ajustar</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Auditoría Card (Solid Dark) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-dark text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Seguimiento</div>
                        <div class="h5 mb-0 font-weight-bold">Auditoría</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=auditoria">Ver Logs</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- Documetos Card (Solid Info - Alternative) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Archivos</div>
                        <div class="h5 mb-0 font-weight-bold">Documentos</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between bg-transparent border-top-0">
                <a class="small text-white stretched-link" href="index.php?action=documentos">Descargar</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../src/Views/layouts/footer.php';
?>
