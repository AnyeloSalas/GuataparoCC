<?php
// src/Views/empresas/index.php

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

// Calculate stats
$total_empresas = count($empresas);
$juridicas = 0;
$otras = 0;

foreach ($empresas as $emp) {
    if ($emp['tipo_rif'] == 'J') {
        $juridicas++;
    } else {
        $otras++;
    }
}
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building text-primary mr-2"></i>Gestión de Empresas
        </h1>
        <a href="index.php?action=empresas_create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Empresa
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if ($success || (isset($_GET['success']) && $_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            $msg = $success ?? $_GET['success'];
            if ($msg == 'created') echo '<i class="fas fa-check-circle mr-2"></i>Empresa creada exitosamente.';
            elseif ($msg == 'updated') echo '<i class="fas fa-check-circle mr-2"></i>Empresa actualizada exitosamente.';
            elseif ($msg == 'deleted') echo '<i class="fas fa-check-circle mr-2"></i>Empresa eliminada exitosamente.';
            else echo htmlspecialchars($msg);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error || (isset($_GET['error']) && $_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            $msg = $error ?? $_GET['error'];
            if ($msg == 'delete_failed') echo '<i class="fas fa-exclamation-circle mr-2"></i>Error al eliminar la empresa.';
            elseif ($msg == 'not_found') echo '<i class="fas fa-exclamation-circle mr-2"></i>Empresa no encontrada.';
            else echo htmlspecialchars($msg);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Content Row -->
    <div class="row">

        <!-- Total Empresas Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Empresas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_empresas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Juridicas Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Jurídicas (J)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $juridicas; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Otras Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Otros Tipos (G, V, E)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $otras; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-2"></i>Listado de Empresas Registradas
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
                <table class="table table-hover mb-0" id="dataTable" cellspacing="0" style="width: auto; min-width: 900px;">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="border-top-0 py-3 text-center" style="width: 150px;">RIF</th>
                            <th class="border-top-0 py-3 pl-4 text-left">Nombre o Razón Social</th>
                            <th class="border-top-0 py-3 text-left">Dirección Fiscal</th>
                            <th class="border-top-0 py-3 text-center" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa): ?>
                        <tr>
                            <td class="align-middle text-center font-weight-bold text-primary">
                                <span class="badge badge-light border text-primary px-3 py-2">
                                    <?php echo htmlspecialchars($empresa['tipo_rif'] . '-' . $empresa['rif_empresa']); ?>
                                </span>
                            </td>
                            <td class="align-middle pl-4 font-weight-bold text-dark">
                                <?php echo htmlspecialchars($empresa['nombre_empresa']); ?>
                            </td>
                            <td class="align-middle text-left text-muted small">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <?php echo htmlspecialchars($empresa['direccion_empresa'] ?: 'Sin dirección registrada'); ?>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown no-arrow d-inline-block">
                                    <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-cog fa-sm text-gray-600"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                        <a class="dropdown-item text-primary" href="index.php?action=empresas_edit&id=<?php echo $empresa['id_empresa']; ?>">
                                            <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Registro
                                        </a>
                                        <form action="index.php?action=empresas_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar esta empresa? \n\n<?php echo htmlspecialchars(addslashes($empresa['nombre_empresa'])); ?>\n\nEsta acción no se puede deshacer.');">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($empresa['id_empresa']); ?>">
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
                                <a class="page-link" href="index.php?action=empresas&page=1" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=empresas&page=<?php echo $pagina - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php 
                            $start = max(1, $pagina - 2);
                            $end = max(1, min($total_paginas, $pagina + 2)); 
                            for ($i = $start; $i <= $end; $i++): 
                            ?>
                                <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=empresas&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=empresas&page=<?php echo $pagina + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=empresas&page=<?php echo max(1, $total_paginas); ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
