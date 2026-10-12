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

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-tie text-primary mr-2"></i>
        Gestión de Postulados
    </h1>
    <div class="d-flex">
        <a href="index.php?action=documentos" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-file-download fa-sm text-white-50"></i> Requisitos
        </a>
        <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
        <a href="index.php?action=postulados_create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Postulado
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($success) || isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
            $msg = $success ?? $_GET['success'];
            if($msg == 'created') echo '<i class="fas fa-check-circle mr-2"></i>Postulado creado exitosamente.';
            elseif($msg == 'updated') echo '<i class="fas fa-check-circle mr-2"></i>Postulado actualizado exitosamente.';
            elseif($msg == 'deleted') echo '<i class="fas fa-check-circle mr-2"></i>Postulado eliminado exitosamente.';
            else echo htmlspecialchars($msg);
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(isset($error) || isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
            $msg = $error ?? $_GET['error'];
            if($msg == 'delete_failed') echo '<i class="fas fa-exclamation-circle mr-2"></i>Error al eliminar el postulado.';
            elseif($msg == 'not_found') echo '<i class="fas fa-exclamation-circle mr-2"></i>Postulado no encontrado.';
            elseif($msg == 'method_not_allowed') echo '<i class="fas fa-exclamation-circle mr-2"></i>Método no permitido. La eliminación requiere petición POST segura.';
            elseif($msg == 'invalid_token') echo '<i class="fas fa-exclamation-circle mr-2"></i>Token de seguridad inválido o expirado. Por favor intente nuevamente.';
            else echo htmlspecialchars($msg);
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
    <?php 
    // Contar postulados
    $total = count($postulados);
    ?>

    <div class="col-xl-12 col-md-12 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Candidatos Postulados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Form -->
<div class="card shadow mb-4 border-left-primary">
    <div class="card-body">
        <form method="GET" action="index.php" class="form-row align-items-end">
            <input type="hidden" name="action" value="postulados">
            
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold text-muted">Nombre o Apellido</label>
                <input type="text" class="form-control form-control-sm" name="search_nombre" 
                       placeholder="Buscar Candidato..." value="<?php echo htmlspecialchars($search['nombre'] ?? ''); ?>">
            </div>

            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-muted">Cédula</label>
                <input type="text" class="form-control form-control-sm" name="search_cedula" 
                       placeholder="Cédula..." value="<?php echo htmlspecialchars($search['cedula'] ?? ''); ?>">
            </div>

            <div class="col-md-5 mb-2">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-search fa-sm mr-1"></i> Filtrar
                </button>
                <?php 
                    $has_filter = !empty($search['nombre']) || !empty($search['cedula']) || !empty($search['keyword']);
                    if($has_filter): 
                ?>
                    <a href="index.php?action=postulados" class="btn btn-secondary btn-sm px-3 ml-1">
                        <i class="fas fa-times fa-sm mr-1"></i> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Postulados -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-2"></i>Lista de Candidatos Postulados
        </h6>
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablaPostulados" width="100%" cellspacing="0">
                <thead class="bg-light text-dark">
                    <tr>
                        <th class="border-top-0 pl-4 py-3" style="width: 80px;">Foto</th>
                        <th class="border-top-0 py-3 text-center">Cédula</th>
                        <th class="border-top-0 py-3">Candidato (Nombre y Apellido)</th>
                        <th class="border-top-0 py-3 d-none d-lg-table-cell">Profesión</th>
                        <th class="border-top-0 py-3 d-none d-lg-table-cell text-center">Referencia</th>
                        <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
                        <th class="border-top-0 pr-4 py-3 text-right" style="width: 120px;">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($postulados)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">

                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No hay postulados registrados</p>
                            <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
                            <a href="index.php?action=postulados_create" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Agregar primer postulado
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($postulados as $p): ?>
                    <tr data-tipo="<?php echo strtoupper($p['tipomiembro']); ?>">
                        <td class="text-center align-middle">
                            <?php if(!empty($p['foto'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" 
                                     alt="Foto" 
                                     class="img-fluid rounded-circle shadow-sm"
                                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #fff;">
                            <?php else: ?>
                                <div class="rounded-circle bg-gray-200 d-inline-flex align-items-center justify-content-center border border-white shadow-sm" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="align-middle text-center">
                            <span class="badge badge-light border text-primary mr-1"><?php echo htmlspecialchars($p['nac_codigo'] ?? 'V'); ?></span>
                            <?php echo htmlspecialchars($p['cedula']); ?>
                        </td>
                        <td class="align-middle"><?php echo htmlspecialchars($p['nombre']); ?></td>
                        <td class="align-middle d-none d-lg-table-cell">
                            <?php echo htmlspecialchars($p['profesion'] ?? '-'); ?>
                        </td>
                        <td class="align-middle d-none d-lg-table-cell text-center">
                            <?php if(!empty($p['tipo_referencia'])): ?>
                                <span class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($p['tipo_referencia']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
                        <td class="pr-4 align-middle text-right">
                            <div class="dropdown no-arrow d-inline-block">
                                <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                    <i class="fas fa-cog fa-sm text-gray-600"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <a class="dropdown-item text-primary" href="index.php?action=postulados_edit&cedula=<?php echo urlencode($p['cedula']); ?>">
                                        <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Postulado
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="index.php?action=postulados_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar este postulado?\n\n<?php echo htmlspecialchars(addslashes($p['nombre'] ?? ($p['primer_nombre'].' '.$p['primer_apellido']))); ?>\n\nEsta acción no se puede deshacer.');">
                                        <?php echo Csrf::tokenField(); ?>
                                        <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($p['cedula']); ?>">
                                        <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                            <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Registro
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                        <?php endif; ?>
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
                        <?php 
                            $query_params = "";
                            if (!empty($search['nombre'])) $query_params .= "&search_nombre=" . urlencode($search['nombre']);
                            if (!empty($search['cedula'])) $query_params .= "&search_cedula=" . urlencode($search['cedula']);
                        ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=postulados&page=1<?php echo $query_params; ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=postulados&page=<?php echo $page - 1; ?><?php echo $query_params; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php 
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                for ($i = $start; $i <= $end; $i++): 
                                ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?action=postulados&page=<?php echo $i; ?><?php echo $query_params; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=postulados&page=<?php echo $page + 1; ?><?php echo $query_params; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=postulados&page=<?php echo $totalPages; ?><?php echo $query_params; ?>" aria-label="Last">
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



<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
