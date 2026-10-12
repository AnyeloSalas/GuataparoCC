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
        <h1 class="h3 mb-0 text-gray-800">Editar Socio</h1>
        <a href="index.php?action=socios" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al listado
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Editar Datos del Socio: <?php echo htmlspecialchars($this->socio->accion); ?></h6>
                </div>
                <div class="card-body">

                    <?php if($msg = flash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $msg; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=socios_update" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
                        <input type="hidden" name="accion" value="<?php echo htmlspecialchars($this->socio->accion); ?>">
                        
                        <div class="form-group">
                            <label for="accion_display"># Accion Socio</label>
                            <input type="text" class="form-control" id="accion_display" value="<?php echo htmlspecialchars($this->socio->accion); ?>" disabled>
                            <small class="form-text text-muted">El número de acción no se puede modificar.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nacionalidad">Nac. <span class="text-danger">*</span></label>
                                    <select class="form-control" id="nacionalidad" name="nacionalidad">
                                        <?php $nac = $this->socio->nacionalidad ?? 'V'; ?>
                                        <option value="V" <?php echo $nac == 'V' ? 'selected' : ''; ?>>V</option>
                                        <option value="E" <?php echo $nac == 'E' ? 'selected' : ''; ?>>E</option>
                                        <option value="P" <?php echo $nac == 'P' ? 'selected' : ''; ?>>P</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="cedula">Cédula de Identidad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['cedula']) ? 'is-invalid' : ''; ?>" 
                                           id="cedula" name="cedula" required
                                           value="<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', old('cedula', $this->socio->cedula))); ?>"
                                           pattern="[0-9]{5,9}" title="Solo números, de 5 a 9 dígitos">
                                    <?php if(isset($errors['cedula'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['cedula']; ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Solo números (sin V- ni guión).</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_nombre">Primer Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['primer_nombre']) ? 'is-invalid' : ''; ?>" 
                                           id="primer_nombre" name="primer_nombre" required
                                           value="<?php echo htmlspecialchars(old('primer_nombre', $this->socio->primer_nombre)); ?>">
                                    <?php if(isset($errors['primer_nombre'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['primer_nombre']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_nombre">Segundo Nombre</label>
                                    <input type="text" class="form-control <?php echo isset($errors['segundo_nombre']) ? 'is-invalid' : ''; ?>" 
                                           id="segundo_nombre" name="segundo_nombre"
                                           value="<?php echo htmlspecialchars(old('segundo_nombre', $this->socio->segundo_nombre)); ?>">
                                    <?php if(isset($errors['segundo_nombre'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['segundo_nombre']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_apellido">Primer Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['primer_apellido']) ? 'is-invalid' : ''; ?>" 
                                           id="primer_apellido" name="primer_apellido" required
                                           value="<?php echo htmlspecialchars(old('primer_apellido', $this->socio->primer_apellido)); ?>">
                                    <?php if(isset($errors['primer_apellido'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['primer_apellido']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_apellido">Segundo Apellido</label>
                                    <input type="text" class="form-control <?php echo isset($errors['segundo_apellido']) ? 'is-invalid' : ''; ?>" 
                                           id="segundo_apellido" name="segundo_apellido"
                                           value="<?php echo htmlspecialchars(old('segundo_apellido', $this->socio->segundo_apellido)); ?>">
                                    <?php if(isset($errors['segundo_apellido'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['segundo_apellido']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email"
                                           value="<?php echo htmlspecialchars(old('email', $this->socio->email ?? '')); ?>">
                                    <?php if(isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control <?php echo isset($errors['estado']) ? 'is-invalid' : ''; ?>" 
                                            id="estado" name="estado">
                                        <option value="ACTIVO" <?php echo (old('estado', $this->socio->estado) == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                        <option value="INACTIVO" <?php echo (old('estado', $this->socio->estado) == 'INACTIVO') ? 'selected' : ''; ?>>INACTIVO</option>
                                        <option value="SUSPENDIDO" <?php echo (old('estado', $this->socio->estado) == 'SUSPENDIDO') ? 'selected' : ''; ?>>SUSPENDIDO</option>
                                    </select>
                                    <?php if(isset($errors['estado'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['estado']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-end">
                            <a href="index.php?action=socios" class="btn btn-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save fa-sm"></i> Actualizar Socio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
// Auto-limpiar: si el usuario pega V-12345, quitar el prefijo
document.getElementById('cedula').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
