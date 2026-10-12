<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$es_admin = (isset($_SESSION['tipousuario']) && $_SESSION['tipousuario'] === 'Administrador');

// Cálculos para métricas
$totalCandidatos = $total_registros ?? count($postulados);
$totalPrincipales = 0;
$totalSuplentes = 0;
$totalEnVotacion = 0;

foreach ($postulados as $p) {
    $tipo = strtoupper($p['tipomiembro'] ?? 'PRINCIPAL');
    if ($tipo === 'PRINCIPAL') $totalPrincipales++;
    elseif ($tipo === 'SUPLENTE') $totalSuplentes++;
    if (!empty($p['en_votacion'])) $totalEnVotacion++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos Postulados - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="assets/vendor/css/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #172554;
            --card-border: #e2e8f0;
            --bg-body: #f8fafc;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Generic elevated card */
        .content-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        }

        .header-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.3rem;
        }

        /* Metric cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 1.1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Table styles */
        .table-custom {
            margin-bottom: 0;
        }

        .table-custom thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .table-custom tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background-color: #f8faff;
        }

        /* Candidate avatar */
        .candidate-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            border: 1.5px solid #e2e8f0;
        }

        .candidate-avatar-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #ffffff;
            background: linear-gradient(135deg, #475569, #334155);
        }

        .nav-link-btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 0.45rem 0.9rem;
        }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0;
            transition: all 0.15s ease;
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="dashboard.php">
                <i class="bi bi-shield-check fs-4 text-primary-emphasis"></i>
                <span>Sistema Guataparo</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?action=votaciones" class="btn btn-sm btn-outline-light nav-link-btn">
                    <i class="bi bi-box-arrow-left me-1"></i> Volver a Votaciones
                </a>
                <a href="dashboard.php" class="btn btn-sm btn-light nav-link-btn">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4 pb-5">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="header-icon bg-primary text-white shadow-sm">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-0 text-slate-900">Candidatos Postulados</h2>
                </div>
                <p class="text-muted small mb-0 ps-md-5">
                    Padrón oficial de candidatos para los procesos electorales y comisiones de la junta directiva.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if($es_admin): ?>
                    <a href="index.php?action=votaciones_configuracion" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold px-3 py-2">
                        <i class="bi bi-gear me-1"></i> Configurar Votación
                    </a>
                    <a href="index.php?action=postulados_create" class="btn btn-success btn-sm rounded-3 fw-semibold px-3 py-2 shadow-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Postulado
                    </a>
                <?php endif; ?>
                <a href="index.php?action=votaciones" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold px-3 py-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block">Total Registrados</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalCandidatos; ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block">Principales</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalPrincipales; ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block">Suplentes</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalSuplentes; ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block">En Boleta Activa</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalEnVotacion; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card with Table -->
        <div class="content-card overflow-hidden">
            
            <!-- Toolbar & Filter Header -->
            <div class="p-3 border-bottom bg-white">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="filtroPostulados" class="form-control border-start-0" 
                                   placeholder="Buscar por cédula, nombre, profesión...">
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-lg-6 d-flex justify-content-md-end gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="filtroEstado" id="fTodos" value="todos" checked>
                            <label class="btn btn-outline-secondary" for="fTodos">Todos</label>

                            <input type="radio" class="btn-check" name="filtroEstado" id="fPrincipales" value="principal">
                            <label class="btn btn-outline-secondary" for="fPrincipales">Principales</label>

                            <input type="radio" class="btn-check" name="filtroEstado" id="fSuplentes" value="suplente">
                            <label class="btn btn-outline-secondary" for="fSuplentes">Suplentes</label>

                            <input type="radio" class="btn-check" name="filtroEstado" id="fEnBoleta" value="en_boleta">
                            <label class="btn btn-outline-secondary" for="fEnBoleta">En Boleta Activa</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-custom table-hover" id="tablaPostulados">
                    <thead>
                        <tr>
                            <th>Candidato</th>
                            <th>Cédula</th>
                            <th>Profesión</th>
                            <th>Correo Electrónico</th>
                            <th class="text-center">Estado Electoral</th>
                            <?php if($es_admin): ?>
                            <th class="text-end" style="min-width: 110px;">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($postulados)): ?>
                            <?php foreach($postulados as $p): 
                                $cedula = htmlspecialchars($p['cedula']);
                                $nombres = htmlspecialchars(trim(($p['primer_nombre'] ?? '') . ' ' . ($p['segundo_nombre'] ?? '')));
                                $apellidos = htmlspecialchars(trim(($p['primer_apellido'] ?? '') . ' ' . ($p['segundo_apellido'] ?? '')));
                                $nombreCompleto = trim($nombres . ' ' . $apellidos);
                                if (empty($nombreCompleto) && !empty($p['nombre'])) {
                                    $nombreCompleto = htmlspecialchars($p['nombre']);
                                }
                                $profesion = htmlspecialchars($p['profesion'] ?? 'Sin especificar');
                                $correo = htmlspecialchars($p['correo_electronico'] ?? '');
                                $tipoMiembro = strtoupper($p['tipomiembro'] ?? 'PRINCIPAL');
                                $enVotacion = !empty($p['en_votacion']);
                                
                                $badgeTipo = ($tipoMiembro === 'PRINCIPAL') 
                                    ? 'bg-success-subtle text-success border border-success-subtle' 
                                    : 'bg-info-subtle text-info-emphasis border border-info-subtle';

                                // Iniciales avatar
                                $iniciales = '';
                                if (!empty($nombres)) $iniciales .= mb_substr($nombres, 0, 1);
                                if (!empty($apellidos)) $iniciales .= mb_substr($apellidos, 0, 1);
                                $iniciales = strtoupper($iniciales) ?: 'C';
                            ?>
                            <tr class="postulado-fila"
                                data-nombre="<?php echo strtolower($nombreCompleto); ?>"
                                data-cedula="<?php echo strtolower($cedula); ?>"
                                data-profesion="<?php echo strtolower($profesion); ?>"
                                data-correo="<?php echo strtolower($correo); ?>"
                                data-tipo="<?php echo strtolower($tipoMiembro); ?>"
                                data-boleta="<?php echo $enVotacion ? '1' : '0'; ?>">

                                <!-- Candidato (Foto + Nombre + Tipo) -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(!empty($p['foto'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" 
                                                 alt="<?php echo $nombreCompleto; ?>" 
                                                 class="candidate-avatar">
                                        <?php else: ?>
                                            <div class="candidate-avatar-placeholder">
                                                <?php echo $iniciales; ?>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <span class="fw-bold text-dark d-block"><?php echo $nombreCompleto; ?></span>
                                            <span class="badge rounded-pill <?php echo $badgeTipo; ?>" style="font-size: 0.7rem;">
                                                <?php echo $tipoMiembro; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cédula -->
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-semibold font-monospace">
                                        <?php echo (!empty($p['nac_codigo']) ? $p['nac_codigo'] . '-' : '') . $cedula; ?>
                                    </span>
                                </td>

                                <!-- Profesión -->
                                <td>
                                    <div class="d-flex align-items-center gap-1 text-muted">
                                        <i class="bi bi-briefcase text-secondary"></i>
                                        <span><?php echo $profesion; ?></span>
                                    </div>
                                </td>

                                <!-- Correo Electrónico -->
                                <td>
                                    <?php if($correo): ?>
                                        <a href="mailto:<?php echo $correo; ?>" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-envelope text-primary"></i>
                                            <span><?php echo $correo; ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No registrado</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado Electoral -->
                                <td class="text-center">
                                    <?php if($enVotacion): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small">
                                            <i class="bi bi-check-circle-fill me-1"></i> En Boleta
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-3 py-1 rounded-pill small">
                                            <i class="bi bi-dash-circle me-1"></i> No Asignado
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Acciones -->
                                <?php if($es_admin): ?>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="index.php?action=postulados_edit&cedula=<?php echo urlencode($p['cedula']); ?>" 
                                           class="btn btn-sm btn-outline-primary btn-action-icon" 
                                           title="Editar Postulado">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-action-icon" 
                                                title="Eliminar Postulado"
                                                onclick="confirmarEliminar('<?php echo $cedula; ?>', '<?php echo addslashes($nombreCompleto); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $es_admin ? '6' : '5'; ?>" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-people fs-1"></i></div>
                                    <h6 class="fw-bold">No hay candidatos postulados registrados</h6>
                                    <p class="text-muted small mb-3">Registra los aspirantes a cargos para incluirlos en el proceso de votación.</p>
                                    <?php if($es_admin): ?>
                                    <a href="index.php?action=postulados_create" class="btn btn-primary btn-sm rounded-pill px-4">
                                        <i class="bi bi-person-plus-fill me-1"></i> Registrar Postulado
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!-- Fila cuando no hay resultados de búsqueda -->
                        <tr id="filaSinCoincidencias" class="d-none">
                            <td colspan="<?php echo $es_admin ? '6' : '5'; ?>" class="text-center py-4 text-muted">
                                <i class="bi bi-search fs-3 d-block mb-1"></i>
                                <span>No se encontraron candidatos que coincidan con la búsqueda.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Card Footer & Pagination -->
            <div class="p-3 bg-light border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Mostrando <strong id="conteoVisibles"><?php echo count($postulados); ?></strong> de <strong><?php echo $totalCandidatos; ?></strong> candidatos registrados
                </div>

                <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-start-pill" href="index.php?action=votaciones_postulados&page=<?php echo $pagina - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php 
                            $start = max(1, $pagina - 2);
                            $end = min($total_paginas, $pagina + 2);
                            for ($i = $start; $i <= $end; $i++): 
                            ?>
                                <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=votaciones_postulados&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-end-pill" href="index.php?action=votaciones_postulados&page=<?php echo $pagina + 1; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Modal de Confirmación para Eliminar Postulado -->
    <div class="modal fade" id="modalEliminarPostulado" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalEliminarLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-x-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="nombrePostuladoEliminar">Nombre del Postulado</h5>
                    <p class="text-muted small mb-3" id="cedulaPostuladoEliminar">C.I. -</p>
                    <div class="alert alert-warning border-0 small text-start mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> Esta acción eliminará permanentemente al candidato del registro de postulados y del tarjetón de votación.
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <form action="index.php?action=votaciones_eliminar_postulado" method="POST" class="d-inline">
                        <?php echo Csrf::tokenField(); ?>
                        <input type="hidden" name="cedula" id="inputCedulaEliminar" value="">
                        <button type="submit" class="btn btn-danger rounded-3 fw-semibold px-3">
                            <i class="bi bi-trash me-1"></i> Sí, Eliminar Postulado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
    <script>
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarPostulado'));

        function confirmarEliminar(cedula, nombre) {
            document.getElementById('inputCedulaEliminar').value = cedula;
            document.getElementById('nombrePostuladoEliminar').textContent = nombre;
            document.getElementById('cedulaPostuladoEliminar').textContent = `Cédula de Identidad: ${cedula}`;
            modalEliminar.show();
        }

        // Filtro y buscador en tiempo real
        const inputFiltro = document.getElementById('filtroPostulados');
        const radiosFiltro = document.querySelectorAll('input[name="filtroEstado"]');
        const filas = document.querySelectorAll('.postulado-fila');
        const filaSinCoincidencias = document.getElementById('filaSinCoincidencias');
        const conteoVisibles = document.getElementById('conteoVisibles');

        function filtrarTabla() {
            const termino = inputFiltro ? inputFiltro.value.toLowerCase().trim() : '';
            const filtroEstado = document.querySelector('input[name="filtroEstado"]:checked')?.value || 'todos';
            let visibles = 0;

            filas.forEach(fila => {
                const nombre = fila.dataset.nombre || '';
                const cedula = fila.dataset.cedula || '';
                const profesion = fila.dataset.profesion || '';
                const correo = fila.dataset.correo || '';
                const tipo = fila.dataset.tipo || '';
                const enBoleta = fila.dataset.boleta === '1';

                const coincideTexto = !termino || nombre.includes(termino) || cedula.includes(termino) || profesion.includes(termino) || correo.includes(termino);

                let coincideEstado = true;
                if (filtroEstado === 'principal') coincideEstado = (tipo === 'principal');
                else if (filtroEstado === 'suplente') coincideEstado = (tipo === 'suplente');
                else if (filtroEstado === 'en_boleta') coincideEstado = enBoleta;

                if (coincideTexto && coincideEstado) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            if (filaSinCoincidencias) {
                filaSinCoincidencias.classList.toggle('d-none', visibles > 0 || filas.length === 0);
            }

            if (conteoVisibles) {
                conteoVisibles.textContent = visibles;
            }
        }

        inputFiltro?.addEventListener('input', filtrarTabla);
        radiosFiltro.forEach(r => r.addEventListener('change', filtrarTabla));
    </script>
</body>
</html>
