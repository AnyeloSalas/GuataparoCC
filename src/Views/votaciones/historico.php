<?php 
include_once PROJECT_ROOT . '/src/Views/layouts/header.php'; 
include_once PROJECT_ROOT . '/src/Views/layouts/sidebar.php'; 
include_once PROJECT_ROOT . '/src/Views/layouts/topbar.php'; 

// Cálculos de métricas para el período seleccionado
$total_candidatos_periodo = !empty($detalles) ? count($detalles) : 0;
$total_admitidos_periodo = 0;
if (!empty($detalles)) {
    foreach ($detalles as $d) {
        if (!empty($d['es_ganador'])) {
            $total_admitidos_periodo++;
        }
    }
}
?>

<style>
/* Estilos modernos para Histórico de Votaciones */
.historico-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
}

.historico-header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

/* Period list items */
.period-item {
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    margin-bottom: 0.5rem;
    background: #ffffff;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
    display: block;
    color: #1e293b;
    position: relative;
}

.period-item:hover {
    border-color: #93c5fd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
    background-color: #f8faff;
    color: #1e293b;
}

.period-item.is-active {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
    border-left: 4px solid #2563eb;
}

/* Stat card in historical detail */
.stat-pill-card {
    border: none;
    border-radius: 14px;
    background: #ffffff;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s;
    height: 100%;
}

.stat-pill-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.stat-pill-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.avatar-initials {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    color: #ffffff;
    background: linear-gradient(135deg, #475569, #334155);
}

.table-custom-historico thead th {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.9rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.table-custom-historico tbody td {
    padding: 0.9rem 1.1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
</style>

<div class="container-fluid py-2">

    <!-- Page Heading -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="historico-header-icon bg-primary text-white shadow-sm">
                    <i class="fas fa-history"></i>
                </div>
                <h1 class="h3 font-weight-bold text-gray-800 mb-0">Histórico de Votaciones</h1>
            </div>
            <p class="text-muted small mb-0 ml-md-5">
                Consulta y audita los procesos electorales finalizados, cómputos oficiales y candidatos admitidos.
            </p>
        </div>
        <div>
            <a href="index.php?action=votaciones" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3 py-2 font-weight-bold">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Volver a Votaciones
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Períodos Archivados -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card historico-card h-100">
                
                <!-- Encabezado con Contador -->
                <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-primary mr-1"><i class="fas fa-archive fs-5"></i></span>
                        <h6 class="m-0 font-weight-bold text-dark">Períodos Archivados</h6>
                    </div>
                    <span class="badge badge-primary rounded-pill px-2 py-1">
                        <?php echo count($periodos); ?> períodos
                    </span>
                </div>

                <!-- Filtro de Fechas y Búsqueda -->
                <div class="p-3 bg-light border-bottom">
                    <!-- Buscador rápido en vivo -->
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-right-0 text-muted">
                            <i class="fas fa-search fa-xs"></i>
                        </span>
                        <input type="text" id="filtroRapidoPeriodos" class="form-control border-left-0" 
                               placeholder="Filtrar por nombre o fecha...">
                    </div>

                    <!-- Filtro por Rango de Fechas -->
                    <form method="GET" action="index.php" class="form-row align-items-center g-1">
                        <input type="hidden" name="action" value="votaciones_historico">
                        <?php if(isset($_GET['periodo_id'])) echo '<input type="hidden" name="periodo_id" value="'.htmlspecialchars($_GET['periodo_id']).'">'; ?>
                        
                        <div class="col-5">
                            <input type="date" name="fecha_desde" class="form-control form-control-sm" 
                                   value="<?php echo htmlspecialchars($_GET['fecha_desde'] ?? ''); ?>" title="Fecha desde">
                        </div>
                        <div class="col-1 text-center text-muted small">-</div>
                        <div class="col-5">
                            <input type="date" name="fecha_hasta" class="form-control form-control-sm" 
                                   value="<?php echo htmlspecialchars($_GET['fecha_hasta'] ?? ''); ?>" title="Fecha hasta">
                        </div>
                        <div class="col-1 text-right">
                            <button type="submit" class="btn btn-primary btn-sm p-1 w-100" title="Buscar por rango">
                                <i class="fas fa-filter fa-xs"></i>
                            </button>
                        </div>

                        <?php if(!empty($_GET['fecha_desde']) || !empty($_GET['fecha_hasta'])): ?>
                            <div class="col-12 mt-1">
                                <a href="index.php?action=votaciones_historico<?php echo isset($_GET['periodo_id']) ? '&periodo_id='.$_GET['periodo_id'] : ''; ?>" 
                                   class="btn btn-outline-secondary btn-sm btn-block py-0" style="font-size: 0.72rem;">
                                    <i class="fas fa-times fa-xs mr-1"></i> Quitar filtro de fechas
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Lista de Períodos -->
                <div class="card-body p-3" style="max-height: 580px; overflow-y: auto;">
                    <?php if (empty($periodos)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-folder-open mb-3 fa-3x text-gray-300"></i>
                            <h6 class="font-weight-bold">Sin períodos archivados</h6>
                            <small>No se encontraron registros de elecciones finalizadas.</small>
                        </div>
                    <?php else: ?>
                        <div id="contenedorPeriodos">
                            <?php foreach ($periodos as $p): 
                                $esActivo = (isset($periodo_id) && $periodo_id == $p['id']);
                                $fInicio = date('d/m/Y', strtotime($p['fecha_inicio']));
                                $fFin = date('d/m/Y', strtotime($p['fecha_fin']));
                                $nombrePeriodo = htmlspecialchars($p['nombre']);
                            ?>
                                <a href="index.php?action=votaciones_historico&periodo_id=<?php echo $p['id']; ?>" 
                                   class="period-item <?php echo $esActivo ? 'is-active' : ''; ?>"
                                   data-search="<?php echo strtolower($nombrePeriodo . ' ' . $fInicio . ' ' . $fFin); ?>">
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="font-weight-bold text-dark text-truncate mr-2" title="<?php echo $nombrePeriodo; ?>">
                                            <?php echo $nombrePeriodo; ?>
                                        </div>
                                        <?php if($esActivo): ?>
                                            <span class="badge badge-primary px-2 py-1 small">Seleccionado</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small text-muted mb-1">
                                        <i class="far fa-calendar-alt text-primary mr-1"></i> 
                                        <span><?php echo $fInicio; ?> al <?php echo $fFin; ?></span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between small text-muted">
                                        <span><i class="fas fa-users text-secondary mr-1"></i> <?php echo number_format($p['total_votantes']); ?> Votantes</span>
                                        <span><i class="fas fa-vote-yea text-secondary mr-1"></i> <?php echo number_format($p['total_votos']); ?> Votos</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div id="noMatchPeriodos" class="text-center py-4 d-none text-muted">
                            <small>No hay períodos que coincidan con la búsqueda.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Detalle de Resultados del Período -->
        <div class="col-xl-8 col-lg-7">
            <?php if (!isset($periodo_seleccionado)): ?>
                <!-- Estado Inicial Vacío -->
                <div class="card historico-card h-100 d-flex align-items-center justify-content-center p-5 text-center">
                    <div class="my-auto py-5">
                        <div class="historico-header-icon bg-primary-subtle text-primary mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem; background-color: #eff6ff;">
                            <i class="fas fa-poll"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark">Seleccione un Período Electoral</h4>
                        <p class="text-muted small mx-auto" style="max-width: 420px;">
                            Haz clic en cualquiera de los períodos archivados del listado izquierdo para auditar los votos emitidos, porcentaje de aceptación y candidatos admitidos.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Período Seleccionado -->
                
                <!-- Encabezado del Período -->
                <div class="card historico-card mb-3 p-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div>
                            <span class="badge badge-success px-3 py-1 rounded-pill mb-1">
                                <i class="fas fa-check-circle mr-1"></i> Proceso Electoral Finalizado
                            </span>
                            <h4 class="font-weight-bold text-dark mb-0"><?php echo htmlspecialchars($periodo_seleccionado['nombre']); ?></h4>
                            <div class="small text-muted mt-1">
                                <i class="far fa-calendar-alt text-primary mr-1"></i>
                                Período de vigencia: <strong><?php echo date('d/m/Y', strtotime($periodo_seleccionado['fecha_inicio'])); ?></strong> al <strong><?php echo date('d/m/Y', strtotime($periodo_seleccionado['fecha_fin'])); ?></strong>
                            </div>
                        </div>
                        <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                            <i class="fas fa-print mr-1"></i> Imprimir Acta
                        </button>
                    </div>
                </div>

                <!-- 4 KPI Cards del Período -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-sm-3 mb-2">
                        <div class="stat-pill-card d-flex align-items-center gap-2">
                            <div class="stat-pill-icon mr-2" style="background-color: #eff6ff; color: #1d4ed8;">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 0.7rem;">Votos</small>
                                <span class="h5 font-weight-bold text-dark mb-0"><?php echo number_format($periodo_seleccionado['total_votos']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3 mb-2">
                        <div class="stat-pill-card d-flex align-items-center gap-2">
                            <div class="stat-pill-icon mr-2" style="background-color: #ecfdf5; color: #047857;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 0.7rem;">Votantes</small>
                                <span class="h5 font-weight-bold text-dark mb-0"><?php echo number_format($periodo_seleccionado['total_votantes']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3 mb-2">
                        <div class="stat-pill-card d-flex align-items-center gap-2">
                            <div class="stat-pill-icon mr-2" style="background-color: #f0f9ff; color: #0284c7;">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 0.7rem;">Postulados</small>
                                <span class="h5 font-weight-bold text-dark mb-0"><?php echo $total_candidatos_periodo; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-3 mb-2">
                        <div class="stat-pill-card d-flex align-items-center gap-2">
                            <div class="stat-pill-icon mr-2" style="background-color: #fefce8; color: #ca8a04;">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 0.7rem;">Admitidos</small>
                                <span class="h5 font-weight-bold text-dark mb-0"><?php echo $total_admitidos_periodo; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Resultados Detallados con Filtros -->
                <div class="card historico-card overflow-hidden mb-4">
                    
                    <!-- Filtro por candidato y categoría -->
                    <div class="p-3 bg-light border-bottom">
                        <form method="GET" action="index.php" class="form-row align-items-center g-2">
                            <input type="hidden" name="action" value="votaciones_historico">
                            <input type="hidden" name="periodo_id" value="<?php echo htmlspecialchars($periodo_id); ?>">
                            
                            <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-right-0 text-muted">
                                        <i class="fas fa-search fa-xs"></i>
                                    </span>
                                    <input type="text" class="form-control border-left-0" name="busqueda" 
                                           placeholder="Buscar candidato por nombre o cédula..." 
                                           value="<?php echo htmlspecialchars($filtros['busqueda'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-8 col-md-4">
                                <select name="tipomiembro" class="form-control form-control-sm">
                                    <option value="">Todas las categorías</option>
                                    <option value="PRINCIPAL" <?php echo ($filtros['tipomiembro'] ?? '') == 'PRINCIPAL' ? 'selected' : ''; ?>>Principal</option>
                                    <option value="SUPLENTE" <?php echo ($filtros['tipomiembro'] ?? '') == 'SUPLENTE' ? 'selected' : ''; ?>>Suplente</option>
                                    <option value="SOCIO" <?php echo ($filtros['tipomiembro'] ?? '') == 'SOCIO' ? 'selected' : ''; ?>>Socio</option>
                                    <option value="CONYUGE" <?php echo ($filtros['tipomiembro'] ?? '') == 'CONYUGE' ? 'selected' : ''; ?>>Cónyuge</option>
                                    <option value="JUVENIL" <?php echo ($filtros['tipomiembro'] ?? '') == 'JUVENIL' ? 'selected' : ''; ?>>Juvenil</option>
                                </select>
                            </div>

                            <div class="col-4 col-md-2 text-right d-flex gap-1 justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm px-2 font-weight-bold" title="Aplicar filtros">
                                    <i class="fas fa-filter fa-xs mr-1"></i> Filtrar
                                </button>
                                <?php if(!empty($filtros['busqueda']) || !empty($filtros['tipomiembro'])): ?>
                                    <a href="index.php?action=votaciones_historico&periodo_id=<?php echo htmlspecialchars($periodo_id); ?>" 
                                       class="btn btn-secondary btn-sm px-2" title="Limpiar Filtros">
                                        <i class="fas fa-times fa-xs"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de Resultados -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom-historico mb-0" id="dataTableHistorico">
                            <thead>
                                <tr>
                                    <th>Candidato Postulado</th>
                                    <th class="text-center">Categoría</th>
                                    <th class="text-center text-success" style="width: 120px;">
                                        <i class="fas fa-thumbs-up mr-1"></i> A Favor
                                    </th>
                                    <th class="text-center text-danger" style="width: 120px;">
                                        <i class="fas fa-thumbs-down mr-1"></i> En Contra
                                    </th>
                                    <th class="text-center" style="width: 160px;">Aceptación</th>
                                    <th class="text-center" style="width: 150px;">Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detalles)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-search mb-2 fa-2x text-gray-300 d-block"></i>
                                            No se encontraron candidatos para los filtros aplicados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($detalles as $d): 
                                        $total_cand_votos = (int)$d['votos_aceptados'] + (int)$d['votos_rechazados'];
                                        $porcentaje_cand = $total_cand_votos > 0 ? round(((int)$d['votos_aceptados'] / $total_cand_votos) * 100, 1) : 0;
                                        
                                        // Iniciales avatar
                                        $palabras = explode(' ', $d['nombre_candidato']);
                                        $iniciales = '';
                                        if (!empty($palabras[0])) $iniciales .= mb_substr($palabras[0], 0, 1);
                                        if (!empty($palabras[1])) $iniciales .= mb_substr($palabras[1], 0, 1);
                                        $iniciales = strtoupper($iniciales) ?: 'C';
                                    ?>
                                        <tr>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar-initials mr-2">
                                                        <?php echo $iniciales; ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($d['nombre_candidato']); ?></div>
                                                        <small class="text-muted">C.I. <?php echo htmlspecialchars($d['cedula_candidato']); ?></small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border text-secondary px-2 py-1 font-weight-bold">
                                                    <?php echo htmlspecialchars($d['tipomiembro']); ?>
                                                </span>
                                            </td>

                                            <td class="align-middle text-center font-weight-bold text-success" style="font-size: 1.05rem;">
                                                <?php echo number_format($d['votos_aceptados']); ?>
                                            </td>

                                            <td class="align-middle text-center font-weight-bold text-danger" style="font-size: 1.05rem;">
                                                <?php echo number_format($d['votos_rechazados']); ?>
                                            </td>

                                            <td class="align-middle text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="progress mr-2 flex-grow-1" style="height: 8px; border-radius: 4px;">
                                                        <div class="progress-bar bg-<?php echo !empty($d['es_ganador']) ? 'success' : 'danger'; ?>" 
                                                             style="width: <?php echo $porcentaje_cand; ?>%;"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-dark"><?php echo $porcentaje_cand; ?>%</span>
                                                </div>
                                            </td>

                                            <td class="align-middle text-center">
                                                <?php if (!empty($d['es_ganador'])): ?>
                                                    <span class="badge badge-success px-3 py-2 shadow-sm rounded-pill font-weight-bold d-inline-block">
                                                        <i class="fas fa-trophy mr-1"></i> ADMITIDO
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger px-3 py-2 shadow-sm rounded-pill font-weight-bold d-inline-block">
                                                        <i class="fas fa-times-circle mr-1"></i> NO ADMITIDO
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once PROJECT_ROOT . '/src/Views/layouts/footer.php'; ?>

<script>
$(document).ready(function() {
    // Buscador dinámico de períodos en la columna izquierda
    $('#filtroRapidoPeriodos').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        let visibles = 0;

        $('.period-item').each(function() {
            const searchData = $(this).data('search') || '';
            if (!query || searchData.includes(query)) {
                $(this).removeClass('d-none');
                visibles++;
            } else {
                $(this).addClass('d-none');
            }
        });

        $('#noMatchPeriodos').toggleClass('d-none', visibles > 0);
    });

    // DataTable para la tabla de resultados detallados
    if ($('#dataTableHistorico').length && $('#dataTableHistorico tbody tr td').length > 1) {
        $('#dataTableHistorico').DataTable({
            "language": {
                "url": "assets/vendor/js/datatables-es-ES.json"
            },
            "order": [[5, "asc"], [2, "desc"]],
            "pageLength": 10,
            "lengthChange": false,
            "searching": false,
            "info": true
        });
    }
});
</script>
