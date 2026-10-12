<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría de Huellas - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="assets/vendor/css/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables BS5 CSS -->
    <link href="assets/vendor/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .premium-header {
            border-start: 4px solid #0d6efd;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .filter-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 15px rgba(0,0,0,0.04);
        }
        .table-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 15px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .table thead th {
            background-color: #ffffff;
            border-bottom: 2px solid #eaebec;
            text-transform: capitalize;
            font-weight: 700;
            color: #001540;
            font-size: 0.85rem;
            padding: 1rem;
        }
        .table tbody td {
            padding: 0.8rem 1rem;
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .badge-tipo {
            font-weight: 600;
            padding: 0.4em 0.8em;
            letter-spacing: 0.3px;
        }
        .btn-buscar {
            background-color: #001540;
            color: white;
        }
        .btn-buscar:hover {
            background-color: #000c24;
            color: white;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <?php require_once __DIR__ . '/../../Views/layouts/navbar.php'; ?>
    </div>

    <div class="container mt-4">
        <!-- Header al estilo Premium -->
        <div class="row mb-4 no-print">
            <div class="col-12">
                <div class="card shadow-sm premium-header border-0 bg-white py-3 px-4 rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 45px; height: 45px;">
                                <i class="bi bi-shield-check fs-5"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Auditoría de Huellas Dactilares</h1>
                                <p class="text-muted mb-0 small">Seguimiento técnico de accesos y eventos biométricos en el sistema.</p>
                            </div>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
                <i class="bi bi-check-circle-fill me-2"></i> Operación realizada con éxito.
                <button type="button" class="btn-close" data-bs-alert="dismiss"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas KPI -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-start border-success border-4 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Logins Exitosos</div>
                        <div class="h4 mb-0 fw-bold text-success"><i class="bi bi-check-circle me-1"></i> <?php echo number_format($estadisticas['logins_exitosos'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-start border-danger border-4 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Logins Fallidos</div>
                        <div class="h4 mb-0 fw-bold text-danger"><i class="bi bi-x-circle me-1"></i> <?php echo number_format($estadisticas['logins_fallidos'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-start border-primary border-4 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Huellas Registradas</div>
                        <div class="h4 mb-0 fw-bold text-primary"><i class="bi bi-fingerprint me-1"></i> <?php echo number_format($estadisticas['registros'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card border-start border-info border-4 h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Similitud Promedio</div>
                        <div class="h4 mb-0 fw-bold text-info"><i class="bi bi-percent me-1"></i> <?php echo number_format($estadisticas['similitud_promedio'] ?? 0, 2); ?>%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Estilo Imagen 1 -->
        <div class="card filter-card mb-4">
            <div class="card-body p-4">
                <form method="GET" action="index.php" class="row g-3">
                    <input type="hidden" name="action" value="auditoria">
                    
                    <div class="col-md-5">
                        <label class="form-label text-muted mb-1 small fw-bold">Tipo de Evento</label>
                        <select name="tipo" class="form-select form-select-sm rounded-2">
                            <option value="todos" <?php echo (!isset($_GET['tipo']) || $_GET['tipo'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="login_exitoso" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'login_exitoso') ? 'selected' : ''; ?>>Login Exitoso</option>
                            <option value="login_fallido" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'login_fallido') ? 'selected' : ''; ?>>Login Fallido</option>
                            <option value="registro" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'registro') ? 'selected' : ''; ?>>Registro</option>
                            <option value="actualizacion" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'actualizacion') ? 'selected' : ''; ?>>Actualización</option>
                            <option value="eliminacion" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'eliminacion') ? 'selected' : ''; ?>>Eliminación</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1 small fw-bold">Límite de Registros</label>
                        <select name="limit" class="form-select form-select-sm rounded-2">
                            <option value="10" <?php echo (!isset($_GET['limit']) || $_GET['limit'] == '10') ? 'selected' : ''; ?>>10 (Predeterminado)</option>
                            <option value="20" <?php echo (isset($_GET['limit']) && $_GET['limit'] == '20') ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo (isset($_GET['limit']) && $_GET['limit'] == '50') ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo (isset($_GET['limit']) && $_GET['limit'] == '100') ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
                        <button type="submit" class="btn btn-sm btn-buscar px-4 rounded-2 h-100" style="height: 31px !important;">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                        
                        <button type="button" onclick="exportarAuditoria('pdf')" class="btn btn-sm btn-danger px-3 rounded-2 h-100" style="background-color: #ff6b6b; border-color: #ff6b6b; height: 31px !important;" title="Exportar a PDF">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                        
                        <button type="button" onclick="exportarAuditoria('excel')" class="btn btn-sm btn-success px-3 rounded-2 h-100" style="background-color: #51cf66; border-color: #51cf66; height: 31px !important;" title="Exportar a Excel">
                            <i class="bi bi-file-earmark-excel"></i>
                        </button>
                        
                        <a href="index.php?action=auditoria" class="btn btn-sm btn-outline-secondary px-2 rounded-2 h-100 d-flex align-items-center" title="Recargar">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>

                        <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-2 h-100 ms-1" data-bs-toggle="modal" data-bs-target="#modalLimpiar" title="Limpiar Registros Antiguos">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Logs -->
        <div class="mb-3">
            <h5 class="fw-bold text-dark fs-5">Resultados (<?php echo count($logs); ?>)</h5>
        </div>

        <div class="card table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="dataTableAuditoria">
                    <thead>
                        <tr>
                            <th class="ps-4">Fecha / Hora</th>
                            <th>Evento</th>
                            <th>Usuario / Cédula</th>
                            <th>Similitud</th>
                            <th>Dirección IP</th>
                            <th class="pe-4">Detalles Técnicos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr style="border-bottom: 1px solid #f1f3f5;">
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo date('d/m/Y', strtotime($log['fecha_hora'])); ?></div>
                                <div class="text-muted small"><?php echo date('H:i:s', strtotime($log['fecha_hora'])); ?></div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'bg-secondary';
                                $icon = 'info-circle';
                                switch($log['tipo_evento']) {
                                    case 'login_exitoso': $badgeClass = 'bg-success'; $icon = 'check-circle-fill'; break;
                                    case 'login_fallido': $badgeClass = 'bg-danger'; $icon = 'x-circle-fill'; break;
                                    case 'registro': $badgeClass = 'bg-primary'; $icon = 'plus-circle-fill'; break;
                                    case 'actualizacion': $badgeClass = 'bg-warning text-dark'; $icon = 'arrow-repeat'; break;
                                    case 'eliminacion': $badgeClass = 'bg-dark'; $icon = 'trash-fill'; break;
                                }
                                ?>
                                <span class="badge badge-tipo rounded-pill <?php echo $badgeClass; ?> bg-opacity-10 text-<?php echo str_replace('bg-', '', explode(' ', $badgeClass)[0]); ?> border border-<?php echo str_replace('bg-', '', explode(' ', $badgeClass)[0]); ?>">
                                    <i class="bi bi-<?php echo $icon; ?> me-1"></i> 
                                    <?php echo strtoupper(str_replace('_', ' ', $log['tipo_evento'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($log['usuario'] ?? 'Sistema'); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($log['cedula'] ?? '-'); ?></div>
                            </td>
                            <td>
                                <?php if($log['similitud_porcentaje']): ?>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px; width: 40px;">
                                            <div class="progress-bar bg-info" style="width: <?php echo $log['similitud_porcentaje']; ?>%"></div>
                                        </div>
                                        <span class="fw-bold text-info" style="font-size: 0.75rem;"><?php echo number_format($log['similitud_porcentaje'], 1); ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td><code class="small text-muted"><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                            <td class="pe-4"><small class="text-muted"><?php echo htmlspecialchars($log['detalles'] ?? ''); ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Limpiar -->
    <div class="modal fade" id="modalLimpiar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Limpiar Auditoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?action=auditoria_limpiar">
                    <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
                    <div class="modal-body p-4">
                        <p class="text-muted">Se eliminarán los registros técnicos más antiguos para optimizar el rendimiento de la base de datos.</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Conservar registros de los últimos:</label>
                            <select name="dias" class="form-select rounded-2">
                                <option value="30">30 días</option>
                                <option value="60">60 días</option>
                                <option value="90" selected>90 días</option>
                                <option value="180">180 días</option>
                                <option value="365">1 año</option>
                            </select>
                        </div>
                        <div class="alert alert-warning border-0 small mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i> Esta acción es permanente y no se puede deshacer.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Proceder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/vendor/js/jquery-3.7.0.min.js"></script>
    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
    <script src="assets/vendor/js/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTableAuditoria').DataTable({
                "language": {
                    "url": "assets/vendor/js/datatables-es-ES.json",
                    "paginate": {
                        "first": "««",
                        "last": "»»",
                        "next": "»",
                        "previous": "«"
                    }
                },
                "order": [[ 0, "desc" ]],
                "pageLength": 10,
                "pagingType": "full_numbers",
                "dom": '<"d-none"f>rt<"d-flex justify-content-between align-items-center p-3"ip>'
            });
        });

        function exportarAuditoria(formato) {
            const form = document.querySelector('form');
            const tipo = form.querySelector('[name="tipo"]').value;
            const limit = form.querySelector('[name="limit"]').value;
            const url = `index.php?action=auditoria_exportar&tipo=${tipo}&limit=${limit}&formato=${formato}`;
            
            if (formato === 'pdf') {
                let printFrame = document.getElementById('print-iframe');
                if (!printFrame) {
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'print-iframe';
                    printFrame.style.position = 'absolute';
                    printFrame.style.top = '-9999px';
                    document.body.appendChild(printFrame);
                }
                printFrame.src = url;
            } else {
                window.open(url, '_blank');
            }
        }
    </script>
</body>
</html>
