<?php
/**
 * Vista del Reporte de Socios
 * 
 * Muestra un listado paginado y filtrable de los socios del club.
 * Permite buscar por nombre, cédula, número de acción, estado, profesión y género.
 * También incluye opciones para exportar los resultados a PDF y Excel.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Socios - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
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

        @media print {
            .no-print {
                display: none !important;
            }

            .hero-section {
                background: none;
                color: black;
                border-radius: 0;
                padding: 1rem 0;
                box-shadow: none;
            }

            .hero-section img {
                filter: none !important;
            }

            .stats-card {
                border: 1px solid #dee2e6;
            }
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
                            <div class="icon-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-circle"
                                style="width: 45px; height: 45px;">
                                <i class="bi bi-graph-up fs-5"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Reporte de Socios</h1>
                                <p class="text-muted mb-0 small">Análisis detallado y gestión de membresías en Guataparo
                                    Country Club.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php?action=reportes"
                                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header para impresión (se mantiene igual o similar) -->
        <div class="d-none d-print-block text-center mb-4">
            <img src="assets/img/logo_guataparo.png" alt="Logo" style="height: 60px; margin-bottom: 10px;">
            <h2 class="fw-bold">Reporte de Socios</h2>
            <p class="text-muted">Guataparo Country Club | Fecha: <?php echo date('d/m/Y'); ?></p>
        </div>




        <!-- Filtros Estilo Referencia -->
        <div class="card filter-card mb-4 border-0 bg-white no-print"
            style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php" id="filterForm">
                    <input type="hidden" name="action" value="reportes_socios">

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Estado</label>
                            <select name="estado" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos</option>
                                <option value="ACTIVO" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                <option value="INACTIVO" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'INACTIVO') ? 'selected' : ''; ?>>INACTIVO</option>
                                <option value="SUSPENDIDO" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'SUSPENDIDO') ? 'selected' : ''; ?>>SUSPENDIDO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Profesión</label>
                            <select name="profesion" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todas</option>
                                <?php foreach ($profesiones as $prof): ?>
                                    <option value="<?php echo htmlspecialchars($prof['nombre']); ?>" <?php echo (isset($_GET['profesion']) && $_GET['profesion'] == $prof['nombre']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Tipo Acción</label>
                            <select name="tipo_accion" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todas</option>
                                <?php foreach ($tipos_accion as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo (isset($_GET['tipo_accion']) && $_GET['tipo_accion'] == $tipo) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tipo); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Género</label>
                            <select name="sexo" class="form-select form-select-sm shadow-none rounded-2">
                                <option value="">Todos</option>
                                <option value="M" <?php echo (isset($_GET['sexo']) && $_GET['sexo'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="F" <?php echo (isset($_GET['sexo']) && $_GET['sexo'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Nombre o
                                Apellido</label>
                            <input type="text" name="busqueda"
                                class="form-control form-control-sm shadow-none rounded-2"
                                placeholder="Nombre o Apellido..."
                                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Cédula</label>
                            <input type="text" name="cedula" class="form-control form-control-sm shadow-none rounded-2"
                                placeholder="Ej: 12345678"
                                value="<?php echo htmlspecialchars($_GET['cedula'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted mb-1" style="font-size: 0.85rem;">Nro Acción</label>
                            <input type="text" name="accion" class="form-control form-control-sm shadow-none rounded-2"
                                placeholder="Ej: 1234" value="<?php echo htmlspecialchars($_GET['accion'] ?? ''); ?>">
                        </div>
                        <div class="col-md-5 d-flex align-items-end justify-content-end gap-2">
                            <button type="submit"
                                class="btn btn-sm d-flex justify-content-center align-items-center rounded-2"
                                style="background-color: #001540; color: white; width: 120px; height: 32px;"
                                title="Buscar">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                            <button type="button"
                                class="btn btn-sm d-flex justify-content-center align-items-center rounded-2"
                                style="background-color: #ff3b00; color: white; width: 100px; height: 32px;"
                                onclick="exportarReporte('pdf')" <?php echo (!isset($is_searching) || !$is_searching || empty($socios)) ? 'disabled' : ''; ?> title="Exportar a PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </button>
                            <button type="button"
                                class="btn btn-sm d-flex justify-content-center align-items-center rounded-2"
                                style="background-color: #198754; color: white; width: 100px; height: 32px;"
                                onclick="exportarReporte('excel')" <?php echo (!isset($is_searching) || !$is_searching || empty($socios)) ? 'disabled' : ''; ?> title="Exportar a Excel">
                                <i class="bi bi-file-earmark-excel"></i>
                            </button>

                            <a href="index.php?action=reportes_socios"
                                class="btn btn-sm text-primary d-flex align-items-center justify-content-center border"
                                style="height: 32px; width: 32px; border-radius: 4px;" title="Limpiar Filtros">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Resultados -->
        <div class="mb-3">
            <h5 class="fw-bold fs-5" style="color: #001540;">Resultados
                (<?php echo isset($total_registros) ? $total_registros : 0; ?>)</h5>
        </div>

        <div class="card table-card border-0" style="box-shadow: 0 1px 15px rgba(0,0,0,0.04); border-radius: 6px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead style="background-color: #ffffff; border-bottom: 2px solid #eaebec;">
                        <tr>
                            <th class="ps-4 border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">#
                                Acción</th>
                            <th class="border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Cédula y
                                Nombre</th>
                            <th class="border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Categoría
                                / Profesión</th>
                            <th class="text-center border-0 text-dark fw-bold text-capitalize"
                                style="font-size: 0.8rem;">Estatus</th>
                            <th class="border-0 text-dark fw-bold text-capitalize" style="font-size: 0.8rem;">Contacto
                            </th>
                            <th class="pe-4 text-end border-0 text-dark fw-bold text-capitalize"
                                style="font-size: 0.8rem;">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!isset($is_searching) || !$is_searching): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted"
                                    style="padding: 6rem 0; background-color: #fafbfc;">
                                    <div class="opacity-50">
                                        <i class="bi bi-funnel d-block mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-normal">Utilice los filtros para realizar una consulta</h6>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif (empty($socios)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted"
                                    style="padding: 6rem 0; background-color: #fafbfc;">
                                    <div class="opacity-50">
                                        <i class="bi bi-search d-block mb-3" style="font-size: 2.5rem;"></i>
                                        <h6 class="fw-normal">No se encontraron socios que coincidan con los filtros
                                            aplicados</h6>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($socios as $socio): ?>
                                <tr style="border-bottom: 1px solid #f1f3f5;">
                                    <td class="ps-4">
                                        <span class="text-dark">
                                            <?php echo htmlspecialchars($socio['accion']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                            <?php echo htmlspecialchars($socio['nombre']); ?></div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars(($socio['nacionalidad'] ?? 'V') . '-' . ($socio['cedula'] ?? '')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($socio['tipo_accion'] ?? 'Asignado'); ?></div>
                                        <div class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($socio['profesion'] ?? 'Sin profesión'); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $bColor = '#6c757d';
                                        if ($socio['estado'] == 'ACTIVO')
                                            $bColor = '#007bff';
                                        elseif ($socio['estado'] == 'SUSPENDIDO')
                                            $bColor = '#dc3545';
                                        elseif ($socio['estado'] == 'INACTIVO')
                                            $bColor = '#343a40';
                                        ?>
                                        <span class="badge rounded-pill"
                                            style="background-color: <?php echo $bColor; ?>; font-weight: 500; font-size: 0.75rem; padding: 0.35em 0.8em; letter-spacing: 0.3px;">
                                            <?php echo htmlspecialchars(ucfirst(strtolower($socio['estado']))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small mb-1"><i
                                                class="bi bi-telephone text-primary me-2"></i><?php echo htmlspecialchars($socio['telefonos'] ?? '-'); ?>
                                        </div>
                                        <div class="small text-muted"><i
                                                class="bi bi-envelope text-primary me-2"></i><?php echo htmlspecialchars($socio['email'] ?? '-'); ?>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="index.php?action=socios_edit&accion=<?php echo $socio['accion']; ?>"
                                            class="btn btn-light btn-sm rounded-circle" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
                        <?php if (isset($is_searching) && $is_searching && count($socios) > 0): ?>
                            Mostrando <?php echo count($socios); ?> de <?php echo $total_registros; ?> registros
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
                                        <a class="page-link shadow-none" href="<?php echo $base_url; ?>&page=1"
                                            title="Primera página">&laquo;&laquo;</a>
                                    </li>

                                    <!-- Previous Page -->
                                    <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none"
                                            href="<?php echo $base_url; ?>&page=<?php echo $pagina - 1; ?>"
                                            title="Página anterior">&laquo;</a>
                                    </li>

                                    <?php
                                    $rango = 2;
                                    $inicio = max(1, $pagina - $rango);
                                    $fin = min($total_paginas, $pagina + $rango);
                                    ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link shadow-none"
                                                href="<?php echo $base_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Page -->
                                    <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none"
                                            href="<?php echo $base_url; ?>&page=<?php echo $pagina + 1; ?>"
                                            title="Página siguiente">&raquo;</a>
                                    </li>

                                    <!-- Last Page -->
                                    <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                                        <a class="page-link shadow-none"
                                            href="<?php echo $base_url; ?>&page=<?php echo $total_paginas; ?>"
                                            title="Última página">&raquo;&raquo;</a>
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
            const url = 'index.php?action=reportes_exportar&tipo=socios&formato=' + formato + '&' + params.toString();

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
                    // Escondemos el print frame pero NO usamos display:none porque algunos navegadores bloquean impresión
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