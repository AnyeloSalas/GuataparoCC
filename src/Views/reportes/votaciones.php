<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Votaciones - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    <script src="assets/vendor/js/chart.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../Views/layouts/navbar.php'; ?>
    
    <div class="container mt-3">
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <img src="assets/img/logo_guataparo.png" alt="Logo" class="me-2" style="height: 40px;">
                    <div>
                        <h4 class="fw-bold mb-0 text-primary">Reporte de Votaciones</h4>
                        <small class="text-muted">Resultados y participación</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?action=reportes" class="btn btn-sm btn-secondary shadow-sm rounded-pill">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <button onclick="exportarReporte('excel')" class="btn btn-sm btn-success shadow-sm rounded-pill">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button onclick="exportarReporte('pdf')" class="btn btn-sm btn-danger shadow-sm rounded-pill">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button onclick="exportarReporte('print')" class="btn btn-sm btn-primary shadow-sm rounded-pill">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
        
        <!-- Estado del Proceso Compacto -->
        <div class="card mb-3 shadow-sm border-0 bg-light">
            <div class="card-body py-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <small class="text-uppercase fw-bold text-muted small">Cronograma electoral</small>
                        <p class="mb-0 small">
                            <strong>Inicio:</strong> <?php echo $fechas['inicio'] ?? '-'; ?> | 
                            <strong>Fin:</strong> <?php echo $fechas['fin'] ?? '-'; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php
                            $now = date('Y-m-d H:i:s');
                            $inicio = $fechas['inicio'] ?? '';
                            $fin = $fechas['fin'] ?? '';
                            
                            if (empty($inicio) || empty($fin)) {
                                echo '<span class="badge bg-secondary rounded-pill">NO CONFIGURADO</span>';
                            } elseif ($now < $inicio) {
                                echo '<span class="badge bg-warning text-dark rounded-pill">PENDIENTE</span>';
                            } elseif ($now > $fin) {
                                echo '<span class="badge bg-danger rounded-pill">FINALIZADO</span>';
                            } else {
                                echo '<span class="badge bg-success rounded-pill animate-pulse">EN CURSO</span>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Participación Slim -->
        <div class="row mb-2">
            <div class="col-md-3 mb-2">
                <div class="card bg-info text-white shadow-sm border-0">
                    <div class="card-body py-2">
                        <small class="opacity-75 d-block">Votos Totales</small>
                        <h4 class="mb-0 fw-bold"><?php echo $estadisticas['total_votos']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body py-2">
                        <small class="opacity-75 d-block">Socios Votantes</small>
                        <h4 class="mb-0 fw-bold"><?php echo $estadisticas['socios_votaron']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-warning text-dark shadow-sm border-0">
                    <div class="card-body py-2">
                        <small class="opacity-75 d-block">Habilitados</small>
                        <h4 class="mb-0 fw-bold"><?php echo $estadisticas['socios_activos']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body py-2">
                        <small class="opacity-75 d-block">Participación</small>
                        <h4 class="mb-0 fw-bold"><?php echo $estadisticas['porcentaje_participacion']; ?>%</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados Compactos -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-3">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-bar-chart-fill me-2"></i>Escrutinio por Candidato</h6>
                        
                        <?php 
                        $currentTipo = '';
                        foreach ($resultados as $candidato): 
                            if ($currentTipo != $candidato['tipomiembro']):
                                $currentTipo = $candidato['tipomiembro'];
                                echo "<div class='bg-light p-1 px-2 rounded mb-2 mt-3 small fw-bold text-uppercase text-secondary'>$currentTipo</div>";
                            endif;
                            
                            $porcentaje = ($estadisticas['total_votos'] > 0) 
                                ? round(($candidato['votos'] / $estadisticas['total_votos']) * 100, 1) 
                                : 0;
                        ?>
                            <div class="mb-2 px-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">
                                        <?php if (!empty($candidato['foto'])): ?>
                                            <?php 
                                                $base64 = base64_encode($candidato['foto']);
                                                $src = 'data:image/jpeg;base64,' . $base64;
                                            ?>
                                            <img src="<?php echo $src; ?>" class="rounded-circle me-1" width="24" height="24" style="object-fit: cover;">
                                        <?php else: ?>
                                            <i class="bi bi-person-circle me-1 text-muted"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($candidato['nombre']); ?>
                                    </span>
                                    <span class="small fw-bold text-dark"><?php echo $candidato['votos']; ?> votos <span class="text-primary">(<?php echo $porcentaje; ?>%)</span></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?php echo $porcentaje; ?>%" 
                                         aria-valuenow="<?php echo $porcentaje; ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($resultados)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay votos registrados aún
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Participación</h5>
                        <canvas id="participacionChart"></canvas>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Votos por Hora</h5>
                        <canvas id="horaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gráfico de Participación
        const participacionCtx = document.getElementById('participacionChart').getContext('2d');
        const stats = <?php echo json_encode($estadisticas); ?>;
        
        new Chart(participacionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Votaron', 'Pendientes'],
                datasets: [{
                    data: [stats.socios_votaron, stats.socios_activos - stats.socios_votaron],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Gráfico de Votos por Hora
        const horaCtx = document.getElementById('horaChart').getContext('2d');
        const horaData = <?php echo json_encode($votosPorHora); ?>;
        
        new Chart(horaCtx, {
            type: 'line',
            data: {
                labels: horaData.map(item => item.hora + ':00'),
                datasets: [{
                    label: 'Votos',
                    data: horaData.map(item => item.total),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        function exportarReporte(formato = 'print') {
            window.open('index.php?action=reportes_exportar&tipo=votaciones&formato=' + formato, '_blank');
        }
    </script>
    
    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
