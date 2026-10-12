<?php
require_once __DIR__ . '/../layouts/header.php';

// Prepare variables set by Controller
$roles_json = json_encode($roles_permisos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$modulosPorCategoria = [];

// Organizar módulos por categoría
if (isset($stmt_modulos)) {
    while ($modulo = $stmt_modulos->fetch(PDO::FETCH_ASSOC)) {
        $modulosPorCategoria[$modulo['categoria']][] = $modulo;
    }
}
?>

<!-- Page Wrapper -->
<div id="wrapper">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-user-lock text-primary mr-2"></i>Gestión de Permisos
                    </h1>
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

                <div class="row">
                    <!-- Selección de Rol -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Tipos de Usuario</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Seleccione un tipo de usuario para configurar sus accesos:</p>
                                <div class="list-group" id="list-tab" role="tablist">
                                    <?php 
                                    $primero = true;
                                    foreach ($roles as $rol): 
                                        if (strtolower($rol) === 'administrador') continue;
                                    ?>
                                        <a class="list-group-item list-group-item-action rol-selector <?php echo $primero ? 'active' : ''; ?>" 
                                           data-rol="<?php echo htmlspecialchars($rol); ?>" 
                                           href="#!">
                                            <i class="fas fa-user-tag mr-2"></i><?php echo htmlspecialchars($rol); ?>
                                        </a>
                                        <?php $primero = false; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Permisos -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Módulos Asignados al Tipo de Usuario: <span id="nombre-rol-actual" class="text-uppercase ml-2 badge badge-info"></span></h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php?action=permisos_update" method="POST" id="form-permisos">
                                    <?php echo Csrf::tokenField(); ?>
                                    <input type="hidden" name="tipousuario" id="input-tipousuario" value="">
                                    
                                    <div class="row">
                                    <?php foreach ($modulosPorCategoria as $categoria => $modulos): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light">
                                                <div class="card-header font-weight-bold">
                                                    <?php echo htmlspecialchars($categoria); ?>
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($modulos as $modulo): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="<?php echo htmlspecialchars($modulo['icono']); ?> mr-2"></i>
                                                            <?php echo htmlspecialchars($modulo['label']); ?>
                                                        </div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input checkbox-modulo" 
                                                                   name="modulos[]" 
                                                                   value="<?php echo $modulo['id']; ?>" 
                                                                   id="mod_<?php echo $modulo['id']; ?>">
                                                            <label class="custom-control-label" for="mod_<?php echo $modulo['id']; ?>"></label>
                                                        </div>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                    
                                    <hr>
                                    <button type="submit" class="btn btn-primary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-save"></i>
                                        </span>
                                        <span class="text">Guardar Permisos</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Initialization Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rolesData = <?php echo $roles_json; ?>;
            const selectors = document.querySelectorAll('.rol-selector');
            const inputTipousuario = document.getElementById('input-tipousuario');
            const labelRolActual = document.getElementById('nombre-rol-actual');
            const checkboxes = document.querySelectorAll('.checkbox-modulo');

            function selectRole(rolName) {
                // Find element
                selectors.forEach(s => s.classList.remove('active'));
                const activeElem = Array.from(selectors).find(s => s.dataset.rol === rolName);
                if (activeElem) activeElem.classList.add('active');

                // Update Hidden input and visual label
                inputTipousuario.value = rolName;
                labelRolActual.textContent = rolName;

                // Reset all checkboxes
                checkboxes.forEach(cb => cb.checked = false);

                // Check checkboxes that correspond to the role's permissions
                const permitidos = rolesData[rolName] || [];
                permitidos.forEach(moduloId => {
                    const cb = document.getElementById('mod_' + moduloId);
                    if(cb) cb.checked = true;
                });
            }

            selectors.forEach(selector => {
                selector.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectRole(this.dataset.rol);
                });
            });

            // Select the first one by default
            const firstActive = document.querySelector('.rol-selector.active');
            if (firstActive) {
                selectRole(firstActive.dataset.rol);
            }
        });
        </script>

        <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

