<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Usuarios - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    <script src="assets/vendor/js/chart.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../Views/layouts/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">
                    <i class="bi bi-person-badge-fill"></i> Reporte de Usuarios
                </h2>
                <p class="text-muted">Listado de operadores y administradores del sistema</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="index.php?action=reportes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <button onclick="exportarReporte()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title opacity-75">Total Usuarios</h6>
                        <h3 class="mb-0"><?php echo $estadisticas['total_usuarios']; ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Distribución por Rol</h5>
                        <div style="height: 150px;">
                            <canvas id="rolesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Puesto de Trabajo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2">
                                                <i class="bi bi-person-fill text-primary"></i>
                                            </div>
                                            <strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
    $badgeClass = 'secondary';
    if ($usuario['tipousuario'] == 'Administrador')
        $badgeClass = 'danger';
    elseif ($usuario['tipousuario'] == 'Operador')
        $badgeClass = 'primary';
?>
                                        <span class="badge bg-<?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($usuario['tipousuario']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['puestotrabajo']); ?></td>
                                </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gráfico de Roles
        const rolesCtx = document.getElementById('rolesChart').getContext('2d');
        const rolesData = <?php echo json_encode($usuariosPorTipo); ?>;
        
        new Chart(rolesCtx, {
            type: 'doughnut',
            data: {
                labels: rolesData.map(item => item.tipousuario),
                datasets: [{
                    data: rolesData.map(item => item.total),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        function exportarReporte() {
            window.open('index.php?action=reportes_exportar&tipo=usuarios', '_blank');
        }
    </script>
    
    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
