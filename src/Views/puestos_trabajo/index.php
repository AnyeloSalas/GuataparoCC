<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
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
        <i class="fas fa-id-badge text-primary mr-2"></i>
        Maestro de Puestos de Trabajo
    </h1>
    <button type="button" class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalCrear">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Puesto
    </button>
</div>

<?php if(isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-2"></i>Listado de Puestos de Trabajo
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
            <table class="table table-hover mb-0" id="dataTable" cellspacing="0" style="width: auto; min-width: 800px;">
                <thead class="bg-light text-dark">
                    <tr>
                        <th class="border-top-0 py-3 text-center" style="width: 80px;">ID</th>
                        <th class="border-top-0 py-3 pl-4 text-left">Nombre del Puesto de Trabajo</th>
                        <th class="border-top-0 py-3 text-center" style="width: 150px;">Estado</th>
                        <th class="border-top-0 py-3 text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($puestos)): ?>
                        <?php foreach($puestos as $puesto): ?>
                        <tr>
                            <td class="align-middle text-center">
                                <div class="badge badge-primary py-2 px-3 shadow-sm">
                                    <?php echo $puesto['id']; ?>
                                </div>
                            </td>
                            <td class="align-middle pl-4 font-weight-bold text-dark"><?php echo htmlspecialchars($puesto['nombre']); ?></td>
                            <td class="align-middle text-center">
                                <?php if($puesto['activo']): ?>
                                    <span class="badge badge-success px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem;">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown no-arrow d-inline-block">
                                    <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-cog fa-sm text-gray-600"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                        <a class="dropdown-item text-primary" href="#" 
                                           data-toggle="modal" data-target="#modalEditar"
                                           data-id="<?php echo $puesto['id']; ?>"
                                           data-nombre="<?php echo htmlspecialchars($puesto['nombre']); ?>"
                                           data-activo="<?php echo $puesto['activo']; ?>">
                                            <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Puesto
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form action="index.php?action=puestos_trabajo_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar este puesto de trabajo permanentemente?');">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($puesto['id']); ?>">
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
    </div>
    <div class="card-footer bg-white py-3 border-top-0">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <small class="text-muted">Total de registros: <strong><?php echo $total_registros; ?></strong></small>
            </div>
            <div class="col-sm-6 text-right">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=puestos_trabajo&page=1" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=puestos_trabajo&page=<?php echo $pagina - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $pagina - 2);
                        $end = max(1, min($total_paginas, $pagina + 2));
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?action=puestos_trabajo&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=puestos_trabajo&page=<?php echo $pagina + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=puestos_trabajo&page=<?php echo max(1, $total_paginas); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-left-success shadow">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-success"><i class="fas fa-plus-circle mr-2"></i>Nuevo Puesto de Trabajo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=puestos_trabajo_create" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre">Nombre del Puesto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required pattern="[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios" placeholder="Ej. Analista de Sistemas">
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" checked value="1">
                            <label class="custom-control-label" for="activo">Puesto Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-left-primary shadow">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary"><i class="fas fa-edit mr-2"></i>Editar Puesto de Trabajo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=puestos_trabajo_update" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nombre">Nombre del Puesto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required pattern="[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios">
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_activo" name="activo" value="1">
                            <label class="custom-control-label" for="edit_activo">Puesto Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt mr-1"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#modalEditar').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nombre = button.data('nombre');
        var activo = button.data('activo');
        
        var modal = $(this);
        modal.find('#edit_id').val(id);
        modal.find('#edit_nombre').val(nombre);
        modal.find('#edit_activo').prop('checked', activo == 1);
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
