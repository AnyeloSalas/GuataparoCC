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

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-heart text-primary mr-2"></i>
        Maestro de Estado Civil
    </h1>
    <a href="index.php?action=estado_civil_create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Estado Civil
    </a>
</div>

<?php if(isset($success) || isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
            $msg = $success ?? $_GET['success'];
            if($msg == 'created') echo '<i class="fas fa-check-circle mr-2"></i>Estado civil creado exitosamente.';
            elseif($msg == 'updated') echo '<i class="fas fa-check-circle mr-2"></i>Estado civil actualizado exitosamente.';
            elseif($msg == 'deleted') echo '<i class="fas fa-check-circle mr-2"></i>Estado civil eliminado exitosamente.';
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
            if($msg == 'delete_failed') echo '<i class="fas fa-exclamation-circle mr-2"></i>Error al eliminar el estado civil.';
            if($msg == 'delete_failed_fk') echo '<i class="fas fa-exclamation-circle mr-2"></i>No se puede eliminar porque está vinculado a registros de socios o familiares.';
            elseif($msg == 'not_found') echo '<i class="fas fa-exclamation-circle mr-2"></i>Estado civil no encontrado.';
            else echo htmlspecialchars($msg);
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-table mr-2"></i>Listado de Estados Civiles
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
                        <th class="border-top-0 py-3 pl-4 text-left">Nombre / Estado Civil</th>
                        <th class="border-top-0 py-3 text-center" style="width: 120px;">Género</th>
                        <th class="border-top-0 py-3 text-left">Descripción</th>
                        <th class="border-top-0 py-3 text-center" style="width: 150px;">Estado</th>
                        <th class="border-top-0 py-3 text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($estados as $e): ?>
                    <tr>
                        <td class="align-middle text-center">
                            <div class="badge badge-primary py-2 px-3 shadow-sm">
                                <?php echo $e['id']; ?>
                            </div>
                        </td>
                        <td class="align-middle pl-4 font-weight-bold text-dark"><?php echo htmlspecialchars($e['nombre'] ?? ''); ?></td>
                        <td class="align-middle text-center">
                            <?php 
                                if($e['genero'] == 'M') echo '<span class="badge badge-info px-3 py-1">Masculino</span>';
                                elseif($e['genero'] == 'F') echo '<span class="badge badge-danger px-3 py-1">Femenino</span>';
                                else echo '<span class="badge badge-secondary px-3 py-1">Ambos</span>';
                            ?>
                        </td>
                        <td class="align-middle text-left text-muted small"><?php echo htmlspecialchars($e['descripcion'] ?? '-'); ?></td>
                        <td class="align-middle text-center">
                            <span class="badge badge-<?php echo $e['activo'] ? 'success' : 'danger'; ?> px-3 py-2 shadow-sm text-uppercase" style="font-size: 0.65rem;">
                                <?php echo $e['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td class="align-middle text-center">
                            <div class="dropdown no-arrow d-inline-block">
                                <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                    <i class="fas fa-cog fa-sm text-gray-600"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                    <a class="dropdown-item text-primary" href="index.php?action=estado_civil_edit&id=<?php echo $e['id']; ?>">
                                        <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Registro
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="index.php?action=estado_civil_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar este estado civil?');">
                                        <?php echo Csrf::tokenField(); ?>
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($e['id']); ?>">
                                        <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                            <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Registro
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                            <a class="page-link" href="index.php?action=estado_civil&page=1" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=estado_civil&page=<?php echo $pagina - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $pagina - 2);
                        $end = max(1, min($total_paginas, $pagina + 2));
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?action=estado_civil&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=estado_civil&page=<?php echo $pagina + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=estado_civil&page=<?php echo max(1, $total_paginas); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
