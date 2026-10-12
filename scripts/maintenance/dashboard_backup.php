<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$tipo = $_SESSION['tipousuario'];

require_once __DIR__ . '/../src/Views/layouts/header.php';
require_once __DIR__ . '/../src/Views/layouts/sidebar.php';
require_once __DIR__ . '/../src/Views/layouts/topbar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Panel Principal</h1>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Bienvenido</h6>
            </div>
            <div class="card-body">
                <p>Has iniciado sesión correctamente como <strong><?php echo htmlspecialchars($usuario); ?></strong>.</p>
                
                <?php if(isset($_GET['error']) && $_GET['error'] == 'acceso_denegado'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Acceso Denegado:</strong> No tiene permisos para acceder a esa sección.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
    <!-- Administrative Modules -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Gestión de Socios</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Administrar</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=socios" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Usuarios</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Administrar</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-id-card fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=usuarios" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cónyuges
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Gestión</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-heart fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=conyugues" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Common Modules -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Votaciones</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Procesos</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-square fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=votaciones" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
    <!-- More Administrative Modules -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Reportes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Estadísticas</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-area fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=reportes" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Configuración</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Sistema</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cogs fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=configuracion" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Auditoría</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Logs</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=auditoria" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Documentos Module - Visible for everyone -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Documentos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Requisitos</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-download fa-2x text-gray-300"></i>
                    </div>
                </div>
                <a href="index.php?action=documentos" class="stretched-link"></a>
            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../src/Views/layouts/footer.php';
?>
