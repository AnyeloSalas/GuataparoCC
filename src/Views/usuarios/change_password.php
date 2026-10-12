<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="assets/vendor/css/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Sistema Guataparo</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="bi bi-key"></i> Cambiar Contraseña</h4>
                    </div>
                    <div class="card-body">
                        <?php if($successMsg = flash('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if($errorMsg = flash('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form action="index.php?action=usuarios_change_password" method="POST" id="formPassword">
                            <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
                            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($this->usuario->usuario); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-person"></i> Usuario
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($this->usuario->usuario); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="bi bi-lock"></i> Contraseña Actual *
                                </label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Nueva Contraseña *
                                </label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Confirmar Nueva Contraseña *
                                </label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required minlength="6">
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Requisitos de seguridad:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Mínimo 6 caracteres</li>
                                    <li>Se recomienda usar letras, números y símbolos</li>
                                    <li>No usar contraseñas obvias</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Cambiar Contraseña
                                </button>
                                <a href="index.php?action=usuarios" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
    <script>
        document.getElementById('formPassword').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirm) {
                e.preventDefault();
                alert('Las contraseñas nuevas no coinciden');
                return false;
            }
        });
    </script>
</body>
</html>
