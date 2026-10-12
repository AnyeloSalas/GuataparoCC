<?php
$pageTitle = "Tipos de Usuario";
require_once PROJECT_ROOT . '/src/Views/layouts/header.php';
?>

<!-- Page Wrapper -->
<div id="wrapper">
    <?php require_once PROJECT_ROOT . '/src/Views/layouts/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <?php require_once PROJECT_ROOT . '/src/Views/layouts/topbar.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-user-tag text-primary mr-2"></i>Tipos de Usuario
                    </h1>
                    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addTipoUsuarioModal">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Nuevo Tipo
                    </button>
                </div>

                <!-- Alert Messages -->
                <?php $flashSuccess = flash('success'); ?>
                <?php $flashError = flash('error'); ?>
                <?php $flashWarning = flash('warning'); ?>
                
                <?php if ($flashSuccess): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <?php echo $flashSuccess; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($flashError): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <?php echo $flashError; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($flashWarning): ?>
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <?php echo $flashWarning; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-table mr-2"></i>Detalle de Tipos (Roles)
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
                                        <th class="border-top-0 py-3 pl-4 text-left">Nombre del Rol / Tipo</th>
                                        <th class="border-top-0 py-3 text-center" style="width: 150px;">Estado</th>
                                        <th class="border-top-0 py-3 text-center" style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : 
                                        $esEspecial = in_array(strtolower($row['nombre']), ['administrador', 'socio']);
                                    ?>
                                        <tr>
                                            <td class="align-middle text-center">
                                                <div class="badge badge-primary py-2 px-3 shadow-sm">
                                                    <?php echo $row['id']; ?>
                                                </div>
                                            </td>
                                            <td class="align-middle pl-4 font-weight-bold text-dark"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                            <td class="align-middle text-center">
                                                <?php if ($row['activo'] == 1) : ?>
                                                    <span class="badge badge-success px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem;">Activo</span>
                                                <?php else : ?>
                                                    <span class="badge badge-danger px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="dropdown no-arrow d-inline-block">
                                                    <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                                        <i class="fas fa-cog fa-sm text-gray-600"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                                        <a class="dropdown-item text-primary edit-btn" href="#"
                                                           data-id="<?php echo $row['id']; ?>"
                                                           data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                           data-activo="<?php echo $row['activo']; ?>"
                                                           data-toggle="modal"
                                                           data-target="#editTipoUsuarioModal">
                                                            <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Registro
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <?php if (!$esEspecial): ?>
                                                        <a class="dropdown-item text-danger delete-btn" href="#"
                                                           data-id="<?php echo $row['id']; ?>"
                                                           data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                           data-toggle="modal"
                                                           data-target="#deleteTipoUsuarioModal">
                                                            <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Registro
                                                        </a>
                                                        <?php else: ?>
                                                        <a class="dropdown-item text-muted disabled" href="#">
                                                            <i class="fas fa-lock fa-sm fa-fw mr-2"></i> Rol Protegido
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <?php require_once PROJECT_ROOT . '/src/Views/layouts/footer.php'; ?>
    </div>
    <!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->

<!-- Add TipoUsuario Modal -->
<div class="modal fade" id="addTipoUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="exampleModalLabel"><i class="fas fa-plus-circle mr-2"></i>Añadir Tipo de Usuario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=tipos_usuario_create" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="nombre" class="font-weight-bold text-dark">Nombre del Rol (Ej: Operador, Analista) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required pattern="[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios" placeholder="Ingrese nombre único">
                    </div>
                     <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-dark" for="activo">Estado Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit TipoUsuario Modal -->
<div class="modal fade" id="editTipoUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title font-weight-bold" id="exampleModalLabel"><i class="fas fa-edit mr-2"></i>Editar Tipo de Usuario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=tipos_usuario_update" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="edit_nombre" class="font-weight-bold text-dark">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required pattern="[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios">
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_activo" name="activo" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="edit_activo">Estado Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-sync-alt mr-1"></i>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete TipoUsuario Modal -->
<div class="modal fade" id="deleteTipoUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="exampleModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=tipos_usuario_delete" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-body p-4">
                    <p class="mb-0 text-dark">¿Está seguro de que desea eliminar el rol <strong id="delete_nombre" class="text-danger"></strong>?</p>
                    <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle mr-1"></i>Al eliminarlo, se borrarán todos sus permisos asignados de la tabla modulos (cascada).</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i>Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                }
            });

            // Edit button click
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                var activo = $(this).data('activo');

                $('#edit_id').val(id);
                $('#edit_nombre').val(nombre);
                if (activo == 1) {
                    $('#edit_activo').prop('checked', true);
                } else {
                    $('#edit_activo').prop('checked', false);
                }
            });

            // Delete button click
            $('.delete-btn').on('click', function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                $('#delete_id').val(id);
                $('#delete_nombre').text(nombre);
            });
        });
    </script>

    <?php require_once PROJECT_ROOT . '/src/Views/layouts/footer.php'; ?>

