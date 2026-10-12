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
    <h1 class="h3 mb-0 text-gray-800">Editar Profesión</h1>
    <a href="index.php?action=profesiones" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
    </a></div>

<?php 
$flash_error = flash('error');
if($flash_error || isset($error)): 
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $flash_error ?? $error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Modificar Profesión: <?php echo htmlspecialchars($profesion->nombre ?? ''); ?></h6>
    </div>
    <div class="card-body">
        <form action="index.php?action=profesiones_edit&id=<?php echo $profesion->id; ?>" method="POST" class="needs-validation" novalidate>
            <?php echo Csrf::tokenField(); ?>
            <div class="form-group col-md-6">
                <label for="nombre">Nombre de la Profesión <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Solo letras y espacios" value="<?php echo htmlspecialchars($profesion->nombre ?? ''); ?>">
                <div class="invalid-feedback">Ingrese un nombre válido (solo letras y espacios).</div>
            </div>
            <div class="form-group col-md-6">
                <label for="activo">Estado</label>
                <select class="form-control" id="activo" name="activo">
                    <option value="1" <?php echo $profesion->activo == 1 ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo $profesion->activo == 0 ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Actualizar Profesión
                </button>
                <a href="index.php?action=profesiones" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
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
