<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Familiares - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="assets/vendor/js/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .stats-card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 2rem;
            opacity: 0.2;
            position: absolute;
            right: 1rem;
            bottom: 0.5rem;
        }
        .filter-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .table-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        /* Nuevos estilos premium */
        .table-hover tbody tr {
            transition: all 0.2s ease;
            position: relative;
        }
        .table-hover tbody tr:hover {
            background-color: #f8faff !important;
            box-shadow: inset 4px 0 0 0 #6f42c1; /* Purple para familiares */
        }
        .avatar-circle {
            background: linear-gradient(135deg, #f1f3f5 0%, #e2e8f0 100%);
            color: #001540;
            text-shadow: 1px 1px 0 rgba(255,255,255,0.5);
            border: 1px solid #ced4da;
        }
        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #6c757d;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        .badge-status {
            font-weight: 600;
            padding: 0.5em 1em;
        }

        @media print {
            .no-print { display: none !important; }
            .hero-section { background: none; color: black; border-radius: 0; padding: 1rem 0; box-shadow: none; }
            .hero-section img { filter: none !important; }
            .stats-card { border: 1px solid #dee2e6; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <?php require_once __DIR__ . '/../../Views/layouts/navbar.php'; ?>
    </div>
    
    <div class="container mt-4">
        <!-- Header al estilo Dashboard -->
        <div class="row mb-4 no-print">
            <div class="col-12">
                <div class="card shadow-sm border-start border-purple border-4 bg-white py-3 px-4 rounded-3" style="border-left-color: #6f42c1 !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle text-white me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 45px; height: 45px; background-color: #6f42c1;">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Reporte de Familiares</h1>
                                <p class="text-muted mb-0 small">Listado y distribución de familiares asociados por estado.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php?action=reportes" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header para impresión -->
        <div class="d-none d-print-block text-center mb-4">
            <img src="assets/img/logo_guataparo.png" alt="Logo" style="height: 60px; margin-bottom: 10px;">
            <h2 class="fw-bold">Reporte de Familiares</h2>
            <p class="text-muted">Guataparo Country Club | Fecha: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <!-- Filtros Estilo Referencia -->
        <div class="card filter-card mb-4 border-0 bg-white no-print" style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php" id="filterForm">
                    <input type="hidden" name="action" value="reportes_conyugues">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Estado Acción</label>
                            <select name="estadoaccion" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos los estados</option>
                                <option value="ACTIVO" <?php echo (isset($_GET['estadoaccion']) && $_GET['estadoaccion'] == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                <option value="INACTIVO" <?php echo (isset($_GET['estadoaccion']) && $_GET['estadoaccion'] == 'INACTIVO') ? 'selected' : ''; ?>>INACTIVO</option>
                                <option value="SUSPENDIDO" <?php echo (isset($_GET['estadoaccion']) && $_GET['estadoaccion'] == 'SUSPENDIDO') ? 'selected' : ''; ?>>SUSPENDIDO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Parentesco</label>
                            <select name="estadocivil" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos los parentescos</option>
                                <option value="Esposa/o" <?php echo (isset($_GET['estadocivil']) && $_GET['estadocivil'] == 'Esposa/o') ? 'selected' : ''; ?>>Esposa/o</option>
                                <option value="Hijo/a" <?php echo (isset($_GET['estadocivil']) && $_GET['estadocivil'] == 'Hijo/a') ? 'selected' : ''; ?>>Hijo/a</option>
                                <option value="Padre" <?php echo (isset($_GET['estadocivil']) && $_GET['estadocivil'] == 'Padre') ? 'selected' : ''; ?>>Padre</option>
                                <option value="Madre" <?php echo (isset($_GET['estadocivil']) && $_GET['estadocivil'] == 'Madre') ? 'selected' : ''; ?>>Madre</option>
                                <option value="Concubino/a" <?php echo (isset($_GET['estadocivil']) && $_GET['estadocivil'] == 'Concubino/a') ? 'selected' : ''; ?>>Concubino/a</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Cédula Titular/Familiar</label>
                            <input type="text" name="cedula" class="form-control form-control-sm shadow-none rounded-2" placeholder="Ej: 12345678" value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Nro Acción</label>
                            <input type="text" name="accion" class="form-control form-control-sm shadow-none rounded-2" placeholder="Ej: 1234" value="<?php echo htmlspecialchars($_GET['accion'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Nombre o Apellido</label>
                            <input type="text" name="busqueda" class="form-control form-control-sm shadow-none rounded-2" placeholder="Nombre o Apellido..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                        </div>
                        <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
                            <button type="submit" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #001540; color: white; width: 120px; height: 32px;" title="Buscar">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #ff3b00; color: white; width: 100px; height: 32px;" onclick="exportarReporte('pdf')" <?php echo (!isset($is_searching) || !$is_searching || empty($conyugues)) ? 'disabled' : ''; ?> title="Exportar a PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </button>
                            <button type="button" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #198754; color: white; width: 100px; height: 32px;" onclick="exportarReporte('excel')" <?php echo (!isset($is_searching) || !$is_searching || empty($conyugues)) ? 'disabled' : ''; ?> title="Exportar a Excel">
                                <i class="bi bi-file-earmark-excel"></i>
                            </button>
                            
                            <a href="index.php?action=reportes_conyugues" class="btn btn-sm text-primary d-flex align-items-center justify-content-center border" style="height: 32px; width: 32px; border-radius: 4px;" title="Limpiar Filtros">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas resumidas -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="card stats-card text-white shadow-sm h-100 position-relative" style="background-color: #6f42c1;">
                    <div class="card-body py-2 px-3">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Total Familiares</small>
                        <h3 class="fs-4 fw-bold mb-0 mt-1"><?php echo $estadisticas['total_conyugues']; ?></h3>
                        <i class="bi bi-people stats-icon" style="font-size: 1.5rem; bottom: 0.3rem;"></i>
                    </div>
                </div>
            </div>
            
            <?php foreach ($conyuguesPorEstado as $estado): ?>
            <div class="col-md-3 mb-2">
                <div class="card stats-card shadow-sm h-100 position-relative
                    <?php 
                        if ($estado['estadoaccion'] == 'ACTIVO') echo 'bg-success';
                        elseif ($estado['estadoaccion'] == 'INACTIVO') echo 'bg-secondary';
                        elseif ($estado['estadoaccion'] == 'SUSPENDIDO') echo 'bg-danger';
                        else echo 'bg-info';
                    ?> text-white">
                    <div class="card-body py-2 px-3">
                        <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;"><?php echo $estado['estadoaccion']; ?></small>
                        <h3 class="fs-4 fw-bold mb-0 mt-1"><?php echo $estado['total']; ?></h3>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Tabla de Resultados -->
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold fs-5" style="color: #001540;">Resultados (<?php echo isset($conyugues) ? count($conyugues) : 0; ?>)</h5>
        </div>
        
        <div class="card table-card border-0 bg-white" style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th class="ps-4">Familiar</th>
                            <th>Sexo / Parentesco</th>
                            <th>Socio Titular</th>
                            <th>Acción</th>
                            <th class="pe-4 text-center">Estado Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!isset($is_searching) || !$is_searching): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 6rem 0; background-color: #fafbfc;">
                                    <div class="opacity-50">
                                        <i class="bi bi-funnel d-block mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-normal">Utilice los filtros para realizar una consulta</h6>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif (empty($conyugues)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 6rem 0; background-color: #fafbfc;">
                                    <div class="opacity-50">
                                        <i class="bi bi-search d-block mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-normal">No se encontraron familiares que coincidan con los filtros aplicados</h6>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($conyugues as $conyugue): ?>
                                <tr style="border-bottom: 1px solid #f1f3f5;">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle me-3 flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; font-weight: bold; font-size: 1.1rem;">
                                                <?php echo substr(htmlspecialchars(trim($conyugue['nombre'] ?? '')), 0, 1); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem; letter-spacing: -0.2px;"><?php echo htmlspecialchars($conyugue['nombre'] ?? ''); ?></div>
                                                <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                                    <i class="bi bi-person-vcard text-secondary"></i> <?php echo htmlspecialchars($conyugue['nacionalidad'] ?? 'V'); ?>-<?php echo htmlspecialchars($conyugue['cedula'] ?? ''); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><?php echo htmlspecialchars($conyugue['sexo'] ?? '-'); ?></div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($conyugue['estadocivil'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?php echo htmlspecialchars($conyugue['nombre_socio'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border fw-bold">#<?php echo htmlspecialchars($conyugue['accion'] ?? ''); ?></span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <?php
                                            $estadoaccion = $conyugue['estadoaccion'] ?? '';
                                            if ($estadoaccion == 'ACTIVO'): ?>
                                                <span class="badge rounded-pill" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                    <i class="bi bi-circle-fill me-1 text-success" style="font-size: 0.4rem; vertical-align: middle;"></i> ACTIVO
                                                </span>
                                            <?php elseif ($estadoaccion == 'INACTIVO'): ?>
                                                <span class="badge rounded-pill" style="background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                    <i class="bi bi-dash-circle me-1" style="font-size: 0.6rem; vertical-align: middle;"></i> INACTIVO
                                                </span>
                                            <?php elseif ($estadoaccion == 'SUSPENDIDO'): ?>
                                                <span class="badge rounded-pill" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                    <i class="bi bi-exclamation-circle-fill me-1 text-danger" style="font-size: 0.5rem; vertical-align: middle;"></i> SUSPENDIDO
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill" style="background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                    <?php echo htmlspecialchars($estadoaccion); ?>
                                                </span>
                                            <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="card-footer bg-white py-3 border-0 no-print" style="border-radius: 0 0 6px 6px;">
                <div class="row align-items-center">
                    <div class="col-md-4 text-muted small">
                        <?php if (isset($is_searching) && $is_searching && count($conyugues) > 0): ?>
                            Mostrando <?php echo count($conyugues); ?> de <?php echo $total_registros; ?> registros
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <?php if (isset($is_searching) && $is_searching && $total_paginas > 1): ?>
                            <style>
                                .custom-paginator .page-link {
                                    color: #4a73e8;
                                    border: 1px solid #dee2e6;
                                    padding: 0.375rem 0.75rem;
                                }
                                .custom-paginator .page-item.active .page-link {
                                    background-color: #4a73e8;
                                    border-color: #4a73e8;
                                    color: white;
                                }
                                .custom-paginator .page-link:hover:not(.disabled) {
                                    background-color: #e9ecef;
                                    color: #2d52bd;
                                }
                                .custom-paginator .page-item.disabled .page-link {
                                    color: #adb5bd;
                                }
                            </style>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm justify-content-end mb-0 custom-paginator">
                                    <?php 
                                        $params = $_GET;
                                        unset($params['page']);
                                        $query = http_build_query($params);
                                        $base_url = "index.php?" . $query;
                                    ?>
                                    
                                    <!-- First Page -->
                                    <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=1" title="Primera página">&laquo;&laquo;</a>
                                    </li>

                                    <!-- Previous Page -->
                                    <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=<?php echo $pagina - 1; ?>" title="Página anterior">&laquo;</a>
                                    </li>

                                    <?php
                                    $rango = 2;
                                    $inicio = max(1, $pagina - $rango);
                                    $fin = min($total_paginas, $pagina + $rango);
                                    ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Page -->
                                    <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=<?php echo $pagina + 1; ?>" title="Página siguiente">&raquo;</a>
                                    </li>
                                    
                                    <!-- Last Page -->
                                    <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=<?php echo $total_paginas; ?>" title="Última página">&raquo;&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function exportarReporte(formato) {
            const params = new URLSearchParams(window.location.search);
            params.delete('action');
            
            if (formato === 'pdf') {
                let printFrame = document.getElementById('print-iframe');
                if (!printFrame) {
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'print-iframe';
                    printFrame.style.position = 'absolute';
                    printFrame.style.top = '-9999px';
                    printFrame.style.width = '1px';
                    printFrame.style.height = '1px';
                    document.body.appendChild(printFrame);
                }
                printFrame.src = 'index.php?action=reportes_exportar&tipo=conyugues&formato=' + formato + '&' + params.toString();
            } else {
                window.open('index.php?action=reportes_exportar&tipo=conyugues&formato=' + formato + '&' + params.toString(), '_blank');
            }
        }
    </script>
    
    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
