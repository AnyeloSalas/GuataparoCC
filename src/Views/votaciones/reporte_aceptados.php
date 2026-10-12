<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 d-print-none">
        <h1 class="h3 mb-0 text-gray-800">Reporte de Candidatos Aceptados</h1>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-print fa-sm text-white-50"></i> Imprimir Reporte
            </button>
            <a href="index.php?action=votaciones_resultados" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Resultados
            </a>
        </div>
    </div>

    <!-- Report Header (Visible in Print) -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <div class="row align-items-center">
                <div class="col-auto">
                    <!-- Placeholder for Club Logo if available -->
                    <div class="h3 font-weight-bold text-dark mb-0">GUATAPARO COUNTRY CLUB</div>
                    <div class="text-xs text-muted">SISTEMA DE GESTIÓN ELECTORAL</div>
                </div>
                <div class="col text-right">
                    <div class="h4 font-weight-bold text-primary mb-0 uppercase">REPORTE FINAL DE ADMISIÓN</div>
                    <div class="small text-muted">Fecha del Reporte: <?php echo date('d/m/Y H:i:s'); ?></div>
                </div>
            </div>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info d-print-none">
                <i class="fas fa-info-circle mr-1"></i> Este reporte muestra únicamente a los candidatos que obtuvieron una mayoría de votos favorables (Aceptados > Rechazados).
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="text-xs font-weight-bold text-uppercase text-muted mb-1">Período de Votación</div>
                    <div class="h6 font-weight-bold text-gray-800">
                        <?php 
                            echo date('d/m/Y', strtotime($fechas['iniciov'])) . ' al ' . date('d/m/Y', strtotime($fechas['finv'])); 
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-xs font-weight-bold text-uppercase text-muted mb-1">Participación Total</div>
                    <div class="h6 font-weight-bold text-gray-800"><?php echo $stats['participacion']; ?>% (<?php echo $stats['votantes_unicos']; ?> de <?php echo $stats['total_socios']; ?> habilitados)</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 150px;">Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Categoría</th>
                            <th class="text-center">Votos Favor</th>
                            <th class="text-center">Votos Contra</th>
                            <th class="text-center">Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($aceptados)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No se encontraron candidatos con resultados favorables en este período.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($aceptados as $a): ?>
                            <tr>
                                <td class="align-middle font-weight-bold"><?php echo htmlspecialchars($a['cedula']); ?></td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <?php if(!empty($a['foto'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($a['foto']); ?>" 
                                                 class="rounded-circle mr-2 d-print-none" style="width: 30px; height: 30px; object-fit: cover;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($a['nombre']); ?>
                                    </div>
                                </td>
                                <td class="align-middle text-uppercase small font-weight-bold"><?php echo htmlspecialchars($a['tipomiembro']); ?></td>
                                <td class="align-middle text-center text-success font-weight-bold"><?php echo $a['aceptados']; ?></td>
                                <td class="align-middle text-center text-danger font-weight-bold"><?php echo $a['rechazados']; ?></td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-success px-3 py-2">ADMITIDO</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section (Visible in Print) -->
            <div class="row mt-5 pt-5 d-none d-print-flex">
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                    <div class="mt-2 small font-weight-bold">JUNTA ELECTORAL</div>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                    <div class="mt-2 small font-weight-bold">PRESIDENCIA</div>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                    <div class="mt-2 small font-weight-bold">SECRETARÍA</div>
                </div>
            </div>

        </div>
        <div class="card-footer bg-light text-center py-3 d-print-none">
            <p class="mb-0 small text-muted font-italic">Este documento es una generación automática del sistema basada en el escrutinio electrónico.</p>
        </div>
    </div>

</div>

<style>
@media print {
    #wrapper #content-wrapper {
        background-color: #fff !important;
    }
    .sidebar, .topbar, .navbar, .d-print-none, .sticky-footer {
        display: none !important;
    }
    .container-fluid {
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-header {
        border-bottom: 2px solid #000 !important;
    }
    .table thead th {
        background-color: #f8f9fc !important;
        color: #000 !important;
        border-bottom: 1px solid #000 !important;
    }
}
</style>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
