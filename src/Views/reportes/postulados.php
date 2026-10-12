<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Postulados - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
        .table thead th {
            background-color: #f1f3f5;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #495057;
            border-bottom: none;
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
        .pagination .page-link {
            border-radius: 0.5rem;
            margin: 0 0.2rem;
            border: none;
            color: #0d47a1;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d47a1;
            color: white;
        }
        
        /* Nuevos estilos premium */
        .table-hover tbody tr {
            transition: all 0.2s ease;
            position: relative;
        }
        .table-hover tbody tr:hover {
            background-color: #f8faff !important;
            box-shadow: inset 4px 0 0 0 #0d47a1;
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
                <div class="card shadow-sm border-start border-primary border-4 bg-white py-3 px-4 rounded-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 45px; height: 45px;">
                                <i class="bi bi-file-person fs-5"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Reporte de Postulados</h1>
                                <p class="text-muted mb-0 small">Análisis detallado y gestión de postulados en Guataparo Country Club.</p>
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
            <h2 class="fw-bold">Reporte de Postulados</h2>
            <p class="text-muted">Guataparo Country Club | Fecha: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <!-- Filtros Estilo Referencia -->
        <div class="card filter-card mb-4 border-0 bg-white no-print" style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php" id="filterForm">
                    <input type="hidden" name="action" value="reportes_postulados">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Tipo Miembro</label>
                            <select name="tipomiembro" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos</option>
                                <option value="PRINCIPAL" <?php echo (isset($_GET['tipomiembro']) && $_GET['tipomiembro'] == 'PRINCIPAL') ? 'selected' : ''; ?>>PRINCIPAL</option>
                                <option value="SUPLENTE" <?php echo (isset($_GET['tipomiembro']) && $_GET['tipomiembro'] == 'SUPLENTE') ? 'selected' : ''; ?>>SUPLENTE</option>
                                <option value="COMISARIO" <?php echo (isset($_GET['tipomiembro']) && $_GET['tipomiembro'] == 'COMISARIO') ? 'selected' : ''; ?>>COMISARIO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Estado Votación</label>
                            <select name="en_votacion" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos</option>
                                <option value="1" <?php echo (isset($_GET['en_votacion']) && $_GET['en_votacion'] === '1') ? 'selected' : ''; ?>>EN VOTACIÓN</option>
                                <option value="0" <?php echo (isset($_GET['en_votacion']) && $_GET['en_votacion'] === '0') ? 'selected' : ''; ?>>NO EN VOTACIÓN</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Cédula</label>
                            <input type="text" name="cedula" class="form-control form-control-sm shadow-none rounded-2" placeholder="Ej: 12345678" value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Nombre o Apellido</label>
                            <input type="text" name="busqueda" class="form-control form-control-sm shadow-none rounded-2" placeholder="Nombre o Apellido..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-12 d-flex align-items-end justify-content-end gap-2">
                            <button type="submit" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #001540; color: white; width: 120px; height: 32px;" title="Buscar">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #ff3b00; color: white; width: 100px; height: 32px;" onclick="exportarReporte('pdf')" <?php echo (empty($postulados)) ? 'disabled' : ''; ?> title="Exportar a PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </button>
                            <button type="button" class="btn btn-sm d-flex justify-content-center align-items-center rounded-2" style="background-color: #198754; color: white; width: 100px; height: 32px;" onclick="exportarReporte('excel')" <?php echo (empty($postulados)) ? 'disabled' : ''; ?> title="Exportar a Excel">
                                <i class="bi bi-file-earmark-excel"></i>
                            </button>
                            
                            <a href="index.php?action=reportes_postulados" class="btn btn-sm text-primary d-flex align-items-center justify-content-center border" style="height: 32px; width: 32px; border-radius: 4px;" title="Limpiar Filtros">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- KPIs Row -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <div class="card stats-card bg-info text-white shadow-sm position-relative mb-0">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.65rem;">Total Postulados</small>
                            <div class="fs-5 fw-bold mb-0 lh-1"><?php echo $estadisticas['total_postulados']; ?></div>
                        </div>
                        <i class="bi bi-people" style="font-size: 1.5rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card stats-card border-0 shadow-sm bg-white mb-0">
                    <div class="card-body p-2">
                        <div class="row align-items-center m-0">
                            <div class="col-md-5 border-end pe-3 py-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Cobertura Votación</small>
                                    <?php 
                                        $porcentajeVotacion = $estadisticas['total_postulados'] > 0 ? round(($estadisticas['en_votacion'] / $estadisticas['total_postulados']) * 100, 1) : 0;
                                    ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;"><?php echo $porcentajeVotacion; ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 3px;">
                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                                         style="width: <?php echo $porcentajeVotacion; ?>%" 
                                         aria-valuenow="<?php echo $porcentajeVotacion; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 ps-3 py-1">
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">En Votación</div>
                                        <div class="fs-6 fw-bold text-dark mb-0 lh-1 mt-1"><?php echo $estadisticas['en_votacion']; ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Finalizados/Otros</div>
                                        <div class="fs-6 fw-bold text-danger mb-0 lh-1 mt-1"><?php echo $estadisticas['total_postulados'] - $estadisticas['en_votacion']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Listado de Resultados -->
        <div class="mb-3">
            <h5 class="fw-bold fs-5" style="color: #001540;">Resultados (<?php echo isset($total_registros) ? $total_registros : 0; ?>)</h5>
        </div>
        
        <div class="card table-card border-0" style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead style="background-color: #ffffff; border-bottom: 2px solid #eaebec;">
                        <tr>
                            <th class="ps-4 border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Cédula y Nombre</th>
                            <th class="border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Tipo / Profesión</th>
                            <th class="border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Referencia</th>
                            <th class="text-center border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Votación</th>
                            <th class="pe-4 border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Contacto</th>
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
                        <?php elseif (empty($postulados)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 6rem 0; background-color: #fafbfc;">
                                    <div class="opacity-50">
                                        <i class="bi bi-search d-block mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-normal">No se encontraron postulados que coincidan con los filtros aplicados</h6>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($postulados as $p): ?>
                                <tr style="border-bottom: 1px solid #f1f3f5;">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle me-3 flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; font-weight: bold; font-size: 1.1rem;">
                                                <?php echo substr(htmlspecialchars(trim($p['nombre'])), 0, 1); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem; letter-spacing: -0.2px;"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                                <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                                    <i class="bi bi-person-vcard text-secondary"></i> <?php echo htmlspecialchars(($p['nacionalidad_codigo'] ?? 'V') . '-' . ($p['cedula'] ?? '')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><?php echo htmlspecialchars($p['tipomiembro'] ?? 'Sin tipo'); ?></div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($p['profesion'] ?? 'Sin profesión'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><?php echo htmlspecialchars($p['tipo_referencia'] ?? '-'); ?></div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($p['referencia_id'] ?? '-'); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['en_votacion']): ?>
                                            <span class="badge rounded-pill" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                <i class="bi bi-circle-fill me-1 text-success" style="font-size: 0.4rem; vertical-align: middle;"></i> EN VOTACIÓN
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill" style="background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; font-weight: 600; font-size: 0.75rem; padding: 0.4em 0.8em; letter-spacing: 0.3px;">
                                                <i class="bi bi-dash-circle me-1" style="font-size: 0.6rem; vertical-align: middle;"></i> FINALIZADO
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4">
                                        <div class="small text-muted"><i class="bi bi-envelope text-primary me-2"></i><?php echo htmlspecialchars($p['correo_electronico'] ?? '-'); ?></div>
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
                        <?php if (isset($is_searching) && $is_searching && count($postulados) > 0): ?>
                            Mostrando <?php echo count($postulados); ?> de <?php echo $total_registros; ?> registros
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
            const url = 'index.php?action=reportes_exportar&tipo=postulados&formato=' + formato + '&' + params.toString();
            
            if (formato === 'pdf') {
                // Usamos un iframe oculto para evitar abrir pestañas en blanco
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
                printFrame.src = url;
            } else {
                window.open(url, '_blank');
            }
        }
    </script>
    
    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
