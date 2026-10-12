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
    <h1 class="h3 mb-0 text-gray-800">Registrar Usuario</h1>
    <a href="index.php?action=usuarios" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<?php 
$flash_error = flash('error');
if($flash_error || isset($error)): 
?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $flash_error ?? $error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Nuevo Usuario</h6>
    </div>
    <div class="card-body">
        <form action="index.php?action=usuarios_create" method="POST" id="formUsuario" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
            
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="usuario" class="font-weight-bold">Nombre de Usuario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control <?php echo Validator::getError('usuario') ? 'is-invalid' : ''; ?>" 
                               id="usuario" name="usuario" required 
                               value="<?php echo htmlspecialchars(old('usuario', '')); ?>"
                               pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guión bajo">
                        <?php if($error = Validator::getError('usuario')): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Sin espacios ni caracteres especiales.</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="email" class="font-weight-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control <?php echo Validator::getError('email') ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" 
                               value="<?php echo htmlspecialchars(old('email', '')); ?>"
                               placeholder="ejemplo@correo.com">
                        <?php if($error = Validator::getError('email')): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-6">
                    <label for="tipousuario" class="font-weight-bold">Tipo de Usuario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                        </div>
                        <select class="form-control <?php echo Validator::getError('tipo_usuario_id') ? 'is-invalid' : ''; ?>" 
                                id="tipo_usuario_id" name="tipo_usuario_id" required>
                            <option value="">Seleccione...</option>
                            <?php if(isset($tipos_usuario)): ?>
                                <?php foreach($tipos_usuario as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t['id']); ?>" <?php echo old('tipo_usuario_id') == $t['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if($error = Validator::getError('tipo_usuario_id')): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group col-md-6">
                    <label for="puesto_trabajo_id" class="font-weight-bold">Puesto de Trabajo</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                        </div>
                        <select class="form-control" id="puesto_trabajo_id" name="puesto_trabajo_id">
                            <option value="">Seleccione Puesto de Trabajo...</option>
                            <?php if(isset($puestos_trabajo)): ?>
                                <?php foreach($puestos_trabajo as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p['id']); ?>" <?php echo old('puesto_trabajo_id') == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-6">
                    <label for="contrasena" class="font-weight-bold">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control <?php echo Validator::getError('contrasena') ? 'is-invalid' : ''; ?>" 
                               id="contrasena" name="contrasena" required minlength="6">
                        <?php if($error = Validator::getError('contrasena')): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Mínimo 6 caracteres.</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="confirm_password" class="font-weight-bold">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                        </div>
                        <input type="password" class="form-control" id="confirm_password" required minlength="6">
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="bloqueado" name="bloqueado" value="1" <?php echo old('bloqueado') ? 'checked' : ''; ?>>
                        <label class="custom-control-label text-danger font-weight-bold" for="bloqueado">
                            Bloquear Usuario (Inhabilitar acceso)
                        </label>
                    </div>
                </div>
            </div>

            <hr class="mt-4">
            
            <div class="row">
                <div class="col-12 text-right">
                    <button type="reset" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save mr-1"></i> Guardar Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const form = document.getElementById('formUsuario');
    const password = document.getElementById('contrasena');
    const confirm = document.getElementById('confirm_password');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        if (password.value !== confirm.value) {
            confirm.setCustomValidity('Las contraseñas no coinciden');
            isValid = false;
        } else {
            confirm.setCustomValidity('');
        }

        if (form.checkValidity() === false || !isValid) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    confirm.addEventListener('input', function() {
        if (password.value !== confirm.value) {
            confirm.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirm.setCustomValidity('');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
