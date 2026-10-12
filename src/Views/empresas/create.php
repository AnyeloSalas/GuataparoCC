<?php
// src/Views/empresas/create.php

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
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building text-primary mr-2"></i>Nueva Empresa
        </h1>
        <a href="index.php?action=empresas" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
        </a>
    </div>

    <!-- Content Row -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-plus-circle mr-2"></i>Formulario de Registro
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    $flash_error = flash('error');
                    if($flash_error || isset($error)): 
                    ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $flash_error ?? $error; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?action=empresas_create" class="needs-validation" novalidate>
                        <?php echo Csrf::tokenField(); ?>
                        
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="tipo_rif" class="font-weight-bold text-gray-700">Tipo RIF <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <select class="form-control" id="tipo_rif" name="tipo_rif" required>
                                        <option value="J">Jurídico (J)</option>
                                        <option value="G">Gubernamental (G)</option>
                                        <option value="V">Personal (V)</option>
                                        <option value="E">Extranjero (E)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-8">
                                <label for="rif_empresa" class="font-weight-bold text-gray-700">Número de RIF <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="rif_empresa" name="rif_empresa" required minlength="9" maxlength="9" placeholder="Ej: 123456789" pattern="[0-9]{9}" title="Debe contener exactamente 9 números">
                                </div>
                                <small class="form-text text-muted">Ingrese exactamente 9 números, sin guiones ni puntos.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nombre_empresa" class="font-weight-bold text-gray-700">Razón Social / Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                </div>
                                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" required maxlength="100" placeholder="Ingrese el nombre completo de la empresa">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="direccion_empresa" class="font-weight-bold text-gray-700">Dirección Fiscal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="direccion_empresa" name="direccion_empresa" maxlength="100" placeholder="Dirección completa">
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?action=empresas" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-2"></i>Guardar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
// Bootstrap validation script
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
