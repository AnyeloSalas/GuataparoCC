<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos complementarios de configuración para enriquecer el panel
$database = new Database();
$db = $database->getConnection();
$configModel = new Configuracion($db);

$porcentaje_req = isset($porcentaje_req) ? (int)$porcentaje_req : ((int)$configModel->get('votacion_porcentaje_aceptacion') ?: 50);
$minimo_votantes = (int)$configModel->get('votacion_minimo_votantes') ?: 0;

$votacionModel = new Votacion($db);
$fechas = $votacionModel->getFechas();

$votantes_unicos = $stats['votantes_unicos'] ?? 0;
$quorum_cumplido = ($minimo_votantes > 0) ? ($votantes_unicos >= $minimo_votantes) : true;
$porcentaje_quorum = ($minimo_votantes > 0) ? min(100, round(($votantes_unicos / $minimo_votantes) * 100, 1)) : 100;

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<style>
/* Estilos modernos para panel de resultados */
.metric-card-elevated {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}

.metric-card-elevated:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -4px rgba(15, 23, 42, 0.09);
}

.metric-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.card-rounded-custom {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
}

.card-header-clean {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
    border-top-left-radius: 16px !important;
    border-top-right-radius: 16px !important;
}

.avatar-thumbnail {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    object-fit: cover;
    border: 1.5px solid #e2e8f0;
}

.avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    color: #ffffff;
    background: linear-gradient(135deg, #475569, #334155);
}

.medal-badge {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: 0.85rem;
}

.medal-gold {
    background: linear-gradient(135deg, #fbbf24, #d97706);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
}

.medal-silver {
    background: linear-gradient(135deg, #94a3b8, #64748b);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3);
}

.medal-bronze {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(180, 83, 9, 0.3);
}

/* Print Styles */
@media print {
    .sidebar,
    nav.navbar.topbar,
    footer,
    #sidebarToggle,
    #sidebarToggleTop,
    .scroll-to-top,
    .btn, a.btn, button.btn, .d-print-none {
        display: none !important;
    }

    body, #wrapper, #content-wrapper, #content, .container-fluid {
        background-color: white !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .card, .metric-card-elevated, .card-rounded-custom {
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        margin-bottom: 1.5rem !important;
        page-break-inside: avoid !important;
    }

    .chart-pie {
        height: auto !important;
        min-height: 220px !important;
        page-break-inside: avoid !important;
    }

    canvas {
        max-width: 100% !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<div class="container-fluid py-2">

    <!-- Page Heading -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="metric-icon-box bg-primary text-white shadow-sm">
                    <i class="fas fa-poll-h"></i>
                </div>
                <h1 class="h3 font-weight-bold text-gray-800 mb-0">Resultados de Votación</h1>
            </div>
            <p class="text-muted small mb-0 ml-md-5">
                Escrutinio automatizado en tiempo real del proceso electoral activo.
                <?php if(!empty($fechas['iniciov']) && !empty($fechas['finv'])): ?>
                    <span class="badge bg-light text-secondary border ml-1">
                        <i class="far fa-calendar-alt mr-1"></i> Período: <?php echo date('d/m/Y', strtotime($fechas['iniciov'])); ?> al <?php echo date('d/m/Y', strtotime($fechas['finv'])); ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="index.php?action=votaciones_reporte_aceptados" class="btn btn-sm btn-success shadow-sm rounded-pill px-3 py-2 font-weight-bold">
                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Reporte de Aceptados
            </a>
            <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3 py-2 font-weight-bold">
                <i class="fas fa-print fa-sm text-white-50 mr-1"></i> Imprimir Resultados
            </button>
            <a href="index.php?action=votaciones" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3 py-2 font-weight-bold">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Banner de Parámetros de Validez (Quórum y Aceptación) -->
    <div class="card card-rounded-custom mb-4 border-0 p-3 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6 border-right">
                <div class="d-flex align-items-center">
                    <div class="metric-icon-box bg-<?php echo $quorum_cumplido ? 'success' : 'warning'; ?>-subtle text-<?php echo $quorum_cumplido ? 'success' : 'warning'; ?> mr-3" style="background-color: <?php echo $quorum_cumplido ? '#d1fae5' : '#fef3c7'; ?>; color: <?php echo $quorum_cumplido ? '#065f46' : '#92400e'; ?>;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div>
                        <div class="small font-weight-bold text-uppercase text-muted">Estado del Quórum Reglamentario</div>
                        <div class="h6 font-weight-bold text-dark mb-0">
                            <?php if($minimo_votantes > 0): ?>
                                <span><?php echo $votantes_unicos; ?> de <?php echo $minimo_votantes; ?> votantes requeridos</span>
                                <?php if($quorum_cumplido): ?>
                                    <span class="badge badge-success ml-2"><i class="fas fa-check-circle mr-1"></i> Quórum Válido</span>
                                <?php else: ?>
                                    <span class="badge badge-warning text-dark ml-2"><i class="fas fa-clock mr-1"></i> En progreso</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span>Sin mínimo de quórum configurado</span>
                                <span class="badge badge-success ml-2">Válido</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center">
                    <div class="metric-icon-box mr-3" style="background-color: #e0f2fe; color: #0369a1;">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <div class="small font-weight-bold text-uppercase text-muted">Criterio de Aprobación Individual</div>
                        <div class="h6 font-weight-bold text-dark mb-0">
                            <span>Umbral mínimo de aceptación: <strong><?php echo $porcentaje_req; ?>%</strong> de votos favorables</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row - 4 KPI Cards -->
    <div class="row g-3 mb-4">

        <!-- Total Votos -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card-elevated p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Total de Votos</div>
                        <div class="h3 font-weight-bold text-dark mb-0"><?php echo number_format($stats['total_votos']); ?></div>
                        <div class="small text-muted mt-1"><i class="fas fa-check-double text-primary mr-1"></i> Sufragios emitidos</div>
                    </div>
                    <div class="metric-icon-box" style="background-color: #eff6ff; color: #1d4ed8;">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Votantes Únicos -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card-elevated p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Votantes Únicos</div>
                        <div class="h3 font-weight-bold text-dark mb-0"><?php echo number_format($stats['votantes_unicos']); ?></div>
                        <div class="small text-muted mt-1"><i class="fas fa-user-check text-success mr-1"></i> Socios que votaron</div>
                    </div>
                    <div class="metric-icon-box" style="background-color: #ecfdf5; color: #047857;">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Socios Habilitados -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card-elevated p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Censo Habilitado</div>
                        <div class="h3 font-weight-bold text-dark mb-0"><?php echo number_format($stats['total_socios']); ?></div>
                        <div class="small text-muted mt-1"><i class="fas fa-id-card text-info mr-1"></i> Acciones activas</div>
                    </div>
                    <div class="metric-icon-box" style="background-color: #f0f9ff; color: #0284c7;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participación -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card-elevated p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div class="small font-weight-bold text-muted text-uppercase mb-1">Participación</div>
                        <div class="h3 font-weight-bold text-dark mb-0"><?php echo $stats['participacion']; ?>%</div>
                    </div>
                    <div class="metric-icon-box" style="background-color: #fefce8; color: #ca8a04;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="progress" style="height: 6px; border-radius: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" 
                         style="width: <?php echo min(100, $stats['participacion']); ?>%" 
                         aria-valuenow="<?php echo $stats['participacion']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Detail Row -->
    <div class="row mb-4">

        <!-- Bar Chart -->
        <div class="col-xl-8 col-lg-7 mb-4 mb-xl-0">
            <div class="card card-rounded-custom h-100">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-primary"><i class="fas fa-chart-bar fs-5"></i></span>
                        <h6 class="m-0 font-weight-bold text-dark">Distribución de Votos por Candidato</h6>
                    </div>
                    <span class="badge badge-light border text-muted small px-2 py-1">
                        <i class="fas fa-sync-alt mr-1"></i> En vivo
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="chart-pie pt-2 pb-2" style="position: relative; height: 320px;">
                        <canvas id="chartResultados"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Candidates Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card card-rounded-custom h-100">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-primary"><i class="fas fa-list-ul fs-5"></i></span>
                        <h6 class="m-0 font-weight-bold text-dark">Resumen por Candidato</h6>
                    </div>
                    <span class="badge badge-primary rounded-pill"><?php echo count($resultados); ?> candidatos</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                <tr>
                                    <th class="py-3 px-3">Candidato</th>
                                    <th class="py-3 text-center text-success" title="Votos Aceptados"><i class="fas fa-check"></i></th>
                                    <th class="py-3 text-center text-danger" title="Votos Rechazados"><i class="fas fa-times"></i></th>
                                    <th class="py-3 text-center text-dark pr-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($resultados as $r): ?>
                                <tr>
                                    <td class="py-2 px-3 align-middle">
                                        <div class="font-weight-bold text-dark small text-truncate" style="max-width: 140px;" title="<?php echo htmlspecialchars($r['nombre']); ?>">
                                            <?php echo htmlspecialchars($r['nombre']); ?>
                                        </div>
                                    </td>
                                    <td class="py-2 text-center text-success font-weight-bold align-middle"><?php echo $r['aceptados']; ?></td>
                                    <td class="py-2 text-center text-danger font-weight-bold align-middle"><?php echo $r['rechazados']; ?></td>
                                    <td class="py-2 text-center font-weight-bold text-dark align-middle pr-3">
                                        <span class="badge bg-light text-dark border"><?php echo $r['votos_totales']; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Escrutinio General Detallado -->
    <div class="card card-rounded-custom mb-4">
        <div class="card-header-clean d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span class="mr-2 text-primary"><i class="fas fa-clipboard-list fs-5"></i></span>
                <div>
                    <h6 class="m-0 font-weight-bold text-dark">Detalle General del Escrutinio por Postulado</h6>
                    <small class="text-muted">Desglose oficial de cómputos, porcentaje de aceptación y estatus según reglamento</small>
                </div>
            </div>
            <div>
                <span class="badge badge-light border text-muted">
                    Umbral reglamentario: <?= $porcentaje_req ?>% de aprobación
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                        <tr>
                            <th class="py-3 px-3 text-center" style="width: 70px;">Pos</th>
                            <th class="py-3">Candidato Postulado</th>
                            <th class="py-3 text-center text-success" style="width: 130px;">Aceptado</th>
                            <th class="py-3 text-center text-danger" style="width: 130px;">Rechazado</th>
                            <th class="py-3 text-center" style="width: 130px;">Total Votos</th>
                            <th class="py-3" style="min-width: 220px;">Aceptación (%)</th>
                            <th class="py-3 text-center pr-3" style="width: 160px;">Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $posicion = 1;
                        foreach($resultados as $r): 
                            $votos_validos = $r['votos_totales'];
                            $porcentaje_aceptacion = $votos_validos > 0 ? round(($r['aceptados'] / $votos_validos) * 100, 2) : 0;
                            $esAprobado = ($porcentaje_aceptacion >= $porcentaje_req);

                            // Iniciales
                            $palabras = explode(' ', $r['nombre']);
                            $iniciales = '';
                            if (!empty($palabras[0])) $iniciales .= mb_substr($palabras[0], 0, 1);
                            if (!empty($palabras[1])) $iniciales .= mb_substr($palabras[1], 0, 1);
                            $iniciales = strtoupper($iniciales) ?: 'C';
                        ?>
                        <tr>
                            <!-- Posición con medalla -->
                            <td class="text-center align-middle px-3">
                                <?php if($posicion == 1 && $votos_validos > 0): ?>
                                    <span class="medal-badge medal-gold" title="Primer lugar">1</span>
                                <?php elseif($posicion == 2 && $votos_validos > 0): ?>
                                    <span class="medal-badge medal-silver" title="Segundo lugar">2</span>
                                <?php elseif($posicion == 3 && $votos_validos > 0): ?>
                                    <span class="medal-badge medal-bronze" title="Tercer lugar">3</span>
                                <?php else: ?>
                                    <span class="font-weight-bold text-muted"><?php echo $posicion; ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Candidato -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if(!empty($r['foto'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($r['foto']); ?>" 
                                             class="avatar-thumbnail mr-3" alt="Foto">
                                    <?php else: ?>
                                        <div class="avatar-fallback mr-3">
                                            <?php echo $iniciales; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($r['nombre']); ?></div>
                                        <small class="text-muted"><i class="fas fa-user-tie mr-1"></i> Postulado</small>
                                    </div>
                                </div>
                            </td>

                            <!-- Votos Aceptados -->
                            <td class="align-middle text-center">
                                <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.95rem;">
                                    <i class="fas fa-check mr-1"></i> <?php echo $r['aceptados']; ?>
                                </span>
                            </td>

                            <!-- Votos Rechazados -->
                            <td class="align-middle text-center">
                                <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 0.95rem;">
                                    <i class="fas fa-times mr-1"></i> <?php echo $r['rechazados']; ?>
                                </span>
                            </td>

                            <!-- Total Votos -->
                            <td class="align-middle text-center">
                                <span class="font-weight-bold text-dark h6 mb-0"><?php echo $r['votos_totales']; ?></span>
                            </td>

                            <!-- Barra de Tendencia -->
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mr-2" style="height: 10px; border-radius: 5px;">
                                        <div class="progress-bar bg-<?php echo $esAprobado ? 'success' : 'danger'; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $porcentaje_aceptacion; ?>%" 
                                             aria-valuenow="<?php echo $porcentaje_aceptacion; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="small font-weight-bold text-dark" style="min-width: 50px;"><?php echo $porcentaje_aceptacion; ?>%</span>
                                </div>
                            </td>

                            <!-- Badge de Estatus -->
                            <td class="align-middle text-center pr-3">
                                <?php if($votos_validos == 0): ?>
                                    <span class="badge badge-light text-muted border px-2 py-1">Sin votos</span>
                                <?php elseif($esAprobado): ?>
                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.8rem;">
                                        <i class="fas fa-check-circle mr-1"></i> Aprobado
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger px-2 py-1" style="font-size: 0.8rem;">
                                        <i class="fas fa-times-circle mr-1"></i> No Alcanza
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                        $posicion++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Script -->
<script src="assets/vendor/js/chart.js"></script>
<script>
    Chart.defaults.font.family = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.color = '#64748b';

    const ctx = document.getElementById('chartResultados');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach($resultados as $r) echo "'" . addslashes($r['nombre']) . "',"; ?>],
                datasets: [
                    {
                        label: 'ACEPTADO',
                        data: [<?php foreach($resultados as $r) echo $r['aceptados'] . ','; ?>],
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'RECHAZADO',
                        data: [<?php foreach($resultados as $r) echo $r['rechazados'] . ', '; ?>],
                        backgroundColor: 'rgba(239, 68, 68, 0.85)',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { left: 10, right: 15, top: 15, bottom: 0 }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            maxTicksLimit: 12,
                            font: { weight: 'bold' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { weight: 'bold' }
                        },
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: { weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 4
                    }
                }
            }
        });
    }
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
