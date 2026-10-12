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
    <h1 class="h3 mb-0 text-gray-800">Editar Usuario</h1>
    <a href="index.php?action=usuarios" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<?php 
$flash_error = flash('error');
if($flash_error || isset($error)): 
?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error ?? $flash_error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Modificar Usuario: <?php echo htmlspecialchars($this->usuario->usuario ?? ''); ?></h6>
    </div>
    <div class="card-body">
        <form action="index.php?action=usuarios_update" method="POST" id="formUsuarioEdit" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($this->usuario->usuario ?? ''); ?>">
            
            <div class="row">
                <div class="form-group col-md-4 text-center text-md-left">
                    <label class="font-weight-bold d-block">Identificador de Usuario</label>
                    <h4 class="text-dark font-weight-bold">
                        <i class="fas fa-user-circle mr-2 text-primary"></i>
                        <?php echo htmlspecialchars($this->usuario->usuario ?? ''); ?>
                    </h4>
                    <span class="badge badge-info mb-2"><i class="fas fa-info-circle mr-1"></i> El nombre de usuario es inamovible</span>
                </div>

                <div class="form-group col-md-4">
                    <label for="email" class="font-weight-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control <?php echo Validator::getError('email') ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" 
                               value="<?php echo htmlspecialchars(old('email', $this->usuario->email) ?? ''); ?>"
                               placeholder="ejemplo@correo.com">
                        <?php if($error = Validator::getError('email')): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="tipousuario" class="font-weight-bold">Rol / Tipo de Usuario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                        </div>
                        <select class="form-control <?php echo Validator::getError('tipo_usuario_id') ? 'is-invalid' : ''; ?>" 
                                id="tipo_usuario_id" name="tipo_usuario_id" required>
                            <?php 
                            $currentTipo = old('tipo_usuario_id', $this->usuario->tipo_usuario_id);
                            ?>
                            <option value="">Seleccione...</option>
                            <?php if(isset($tipos_usuario)): ?>
                                <?php foreach($tipos_usuario as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t['id']); ?>" <?php echo ($currentTipo == $t['id']) ? 'selected' : ''; ?>>
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
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-8">
                    <label for="puesto_trabajo_id" class="font-weight-bold">Puesto de Trabajo / Cargo</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                        </div>
                        <select class="form-control" id="puesto_trabajo_id" name="puesto_trabajo_id">
                            <option value="">Seleccione Puesto de Trabajo...</option>
                            <?php if(isset($puestos_trabajo)): ?>
                                <?php foreach($puestos_trabajo as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p['id']); ?>" 
                                        <?php echo old('puesto_trabajo_id', $this->usuario->puesto_trabajo_id ?? '') == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group col-md-4 d-flex align-items-end">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="bloqueado" name="bloqueado" value="1" 
                            <?php echo old('bloqueado', $this->usuario->bloqueado ?? 0) == 1 ? 'checked' : ''; ?>>
                        <label class="custom-control-label text-danger font-weight-bold" for="bloqueado">
                            Inhabilitar Acceso (Bloqueado)
                        </label>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-4 border-left-warning shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                    <div>
                        <strong>Seguridad de la Cuenta:</strong>
                        <p class="mb-0">Para modificar la clave de acceso, debe dirigirse a la opción <a href="index.php?action=usuarios_change_password&usuario=<?php echo urlencode($this->usuario->usuario); ?>" class="alert-link">Cambiar Contraseña</a> desde la lista principal.</p>
                    </div>
                </div>
            </div>

            <hr class="mt-4">
            
            <div class="row">
                <div class="col-12 text-right">
                    <a href="index.php?action=usuarios" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Actualizar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    const form = document.getElementById('formUsuarioEdit');
    form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
