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
    <h1 class="h3 mb-0 text-gray-800">Nueva Nacionalidad</h1>
    <a href="index.php?action=nacionalidades" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
    </a></div>

<?php 
$flash_error = flash('error');
$flash_success = flash('success');
if($flash_error || isset($error)): 
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $flash_error ?? $error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
<?php if($flash_success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?php echo $flash_success; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Nacionalidad</h6>
    </div>
    <div class="card-body">
        <form action="index.php?action=nacionalidades_create" method="POST" class="needs-validation" novalidate>
            <?php echo Csrf::tokenField(); ?>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="codigo" class="font-weight-bold text-dark">Código (Ej: V)</label>
                    <input type="text" name="codigo" id="codigo" class="form-control" required pattern="[a-zA-Z]{1}" maxlength="1" title="Debe ingresar exactamente una letra" maxlength="5" placeholder="Código corto">
                </div>
                <div class="col-md-8 form-group">
                    <label for="nombre" class="font-weight-bold text-dark">Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required pattern="[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios" maxlength="100" placeholder="Ej: Venezolana">
                </div>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo" name="activo" checked>
                    <label class="custom-control-label" for="activo">Registro Activo</label>
                </div>
            </div>

            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i> Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
