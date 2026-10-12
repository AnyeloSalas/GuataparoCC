<?php
/**
 * Dashboard Principal de Reportes
 * 
 * Esta vista muestra un resumen general de las estadísticas del sistema
 * (KPIs) y proporciona accesos directos a los reportes detallados de
 * Socios, Familiares, Huellas, Votaciones y Postulados.
 */

// Asegurarse de que la sesión esté iniciada y el usuario tenga permisos
// (El controlador correspondiente ya debería haber validado esto)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar layouts (cabecera, barra lateral y barra superior)
require_once __DIR__ . '/../../Views/layouts/header.php';
require_once __DIR__ . '/../../Views/layouts/sidebar.php';
require_once __DIR__ . '/../../Views/layouts/topbar.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-line text-primary"></i> Dashboard de Reportes</h1>
    </div>

    <!-- Content Row - Estadísticas KPI -->
    <div class="row">

        <!-- Total Socios Card -->
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Socios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $estadisticas['total_socios']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Familiares Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Familiares
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $estadisticas['total_conyugues']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Huellas Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Huellas</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        <?php echo $estadisticas['total_huellas']; ?></div>
                                </div>
                                <div class="col">
                                    <?php
                                    $porcent = ($estadisticas['total_socios'] > 0) ? round(($estadisticas['total_huellas'] / $estadisticas['total_socios']) * 100) : 0;
                                    ?>
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: <?php echo $porcent; ?>%"
                                            aria-valuenow="<?php echo $porcent; ?>" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-fingerprint fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Votaciones Card -->
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Votaciones</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $estadisticas['total_votaciones']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vote-yea fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Postulados Card -->
        <div class="col-xl-2 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Postulados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $estadisticas['total_postulados']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Content Row - Accesos Directos -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reportes Detallados</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-6 mb-3">
                            <a href="index.php?action=reportes_socios"
                                class="btn btn-outline-primary btn-block p-4 shadow-sm hover-lift h-100">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <div class="small fw-bold">Socios</div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-3">
                            <a href="index.php?action=reportes_votaciones"
                                class="btn btn-outline-warning btn-block p-4 shadow-sm hover-lift h-100">
                                <i class="fas fa-vote-yea fa-2x mb-2"></i>
                                <div class="small fw-bold">Votaciones</div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="index.php?action=reportes_conyugues"
                                class="btn btn-outline-success btn-block p-4 shadow-sm hover-lift h-100">
                                <i class="fas fa-user-friends fa-2x mb-2"></i>
                                <div class="small fw-bold">Familiares</div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-3">
                            <a href="index.php?action=reportes_huellas"
                                class="btn btn-outline-info btn-block p-4 shadow-sm hover-lift h-100">
                                <i class="fas fa-fingerprint fa-2x mb-2"></i>
                                <div class="small fw-bold">Huellas</div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="index.php?action=reportes_postulados"
                                class="btn btn-outline-danger btn-block p-4 shadow-sm hover-lift h-100">
                                <i class="fas fa-id-card fa-2x mb-2"></i>
                                <div class="small fw-bold">Postulados</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- Incluir Chart.js -->
<script src="assets/vendor/js/chart.js"></script>

<script>
    // Configuración común
    Chart.defaults.font.family = 'Nunito, -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#858796';
</script>

<style>
    /* Efecto hover suave para los botones grandes */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<?php require_once __DIR__ . '/../../Views/layouts/footer.php'; ?>