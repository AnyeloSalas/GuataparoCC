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
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Censo de Familiares</h1>
        <div class="buttons-header">
            <a href="index.php?action=socios" class="btn btn-sm btn-secondary shadow-sm mr-2">
                <i class="fas fa-users fa-sm text-white-50"></i> Ver Socios
            </a>
            <a href="index.php?action=conyugues_create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Nuevo Familiar
            </a>
        </div>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-left-danger shadow" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> Error al procesar la solicitud del familiar.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search & Filters -->
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="action" value="conyugues">
                <div class="row align-items-center">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary text-white border-primary">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control border-primary" id="search" name="search" 
                                   placeholder="Buscar por cédula, nombre o # Accion Socio..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block shadow-sm">
                            Filtrar Listado
                        </button>
                    </div>
                </div>
                <?php if(!empty($_GET['search'])): ?>
                    <div class="mt-2">
                        <span class="text-xs text-muted">Mostrando resultados para: <strong>"<?php echo htmlspecialchars($_GET['search']); ?>"</strong></span>
                        <a href="index.php?action=conyugues" class="ml-2 text-danger text-xs text-uppercase font-weight-bold">
                            <i class="fas fa-sync-alt fa-xs"></i> Limpiar Filtro
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table mr-2"></i>Registros del Grupo Familiar</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Exportar:</div>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-danger"></i> PDF (Próximamente)</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-excel fa-sm fa-fw mr-2 text-success"></i> Excel (Próximamente)</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="border-top-0 py-3 text-center"># Accion Socio</th>
                            <th class="border-top-0 py-3">Cédula</th>
                             <th class="border-top-0 py-3">Nombres</th>
                            <th class="border-top-0 py-3">Apellidos</th>
                            <th class="border-top-0 py-3 text-center">Referencia / Parentesco</th>
                            <th class="border-top-0 py-3 d-none d-sm-table-cell text-center">Estado Civil</th>
                            <th class="border-top-0 pr-4 py-3 text-right" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($conyugues)): ?>
                            <tr>
                                 <td colspan="7" class="text-center py-5">
                                    <img src="img/undraw_no_data.svg" style="width: 150px; opacity: 0.5;" alt="No data">
                                    <p class="mt-3 text-gray-500 font-italic">No se encontraron familiares registrados.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($conyugues as $conyugue): ?>
                            <tr>
                                <td class="align-middle text-center">
                                    <div class="badge badge-secondary py-2 px-3 shadow-sm">
                                        <i class="fas fa-id-badge mr-1"></i> <?php echo htmlspecialchars($conyugue['accionconyugue']); ?>
                                    </div>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">
                                    <span class="badge badge-light border text-primary mr-1"><?php echo htmlspecialchars($conyugue['nacionalidad_codigo'] ?? 'V'); ?></span>
                                    <?php echo htmlspecialchars($conyugue['cedulaconyugue']); ?>
                                </td>
                                 <td class="align-middle">
                                    <?php echo htmlspecialchars(trim(($conyugue['primer_nombre'] ?? '') . ' ' . ($conyugue['segundo_nombre'] ?? ''))); ?>
                                </td>
                                <td class="align-middle">
                                    <?php echo htmlspecialchars(trim(($conyugue['primer_apellido'] ?? '') . ' ' . ($conyugue['segundo_apellido'] ?? ''))); ?>
                                </td>
                                <td class="align-middle text-center">
                                    <?php 
                                        $rel = $conyugue['tipo_relacion'] ?? 'Otro';
                                        $badgeClass = 'badge-primary';
                                        if($rel == 'Esposa/o') $badgeClass = 'badge-success';
                                        if($rel == 'Concubino/a') $badgeClass = 'badge-warning';
                                        if($rel == 'Hijo/a') $badgeClass = 'badge-info';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-link fa-xs mr-1 opacity-5"></i> <?php echo htmlspecialchars($rel); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center d-none d-sm-table-cell">
                                    <span class="text-xs font-weight-bold text-gray-700">
                                        <?php echo htmlspecialchars($conyugue['estado_civil'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="pr-4 align-middle text-right">
                                    <div class="dropdown no-arrow d-inline-block">
                                        <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-cog fa-sm text-gray-600"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                            <a class="dropdown-item text-primary" href="index.php?action=conyugues_edit&accion=<?php echo $conyugue['accionconyugue']; ?>">
                                                <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Familiar
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form action="index.php?action=conyugues_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar permanentemente a este familiar? Esta acción no se puede deshacer.');">
                                                <?php echo Csrf::tokenField(); ?>
                                                <input type="hidden" name="accion" value="<?php echo htmlspecialchars($conyugue['accionconyugue']); ?>">
                                                <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                                    <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Registro
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <small class="text-muted">Total de registros: <strong><?php echo $totalRecords; ?></strong></small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm justify-content-end mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="index.php?action=conyugues&page=1<?php echo $search ? '&search='.urlencode($search) : ''; ?>" aria-label="First">
                                            <span aria-hidden="true">&laquo;&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="index.php?action=conyugues&page=<?php echo $page - 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php 
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++): 
                                    ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="index.php?action=conyugues&page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="index.php?action=conyugues&page=<?php echo $page + 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="index.php?action=conyugues&page=<?php echo $totalPages; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" aria-label="Last">
                                            <span aria-hidden="true">&raquo;&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
