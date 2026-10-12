<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Socio - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('assets/img/img12.JPG');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background-color: rgba(51, 51, 51, 0.95);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .form-control {
            background-color: #e6e6e6;
            border: none;
            height: 45px;
        }
        .btn-primary {
            height: 45px;
            font-weight: bold;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="text-center mb-4">
            <img src="assets/img/logo_guataparo.png" alt="Guataparo Country Club" style="max-width: 200px;">
            <h3 class="mt-3">Registro de Socio</h3>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div class="mt-2">
                    <a href="index.php" class="btn btn-sm btn-success">Ir al Login</a>
                </div>
            </div>
        <?php else: ?>

        <form action="index.php?action=register_socio" method="POST">
            <?php echo Csrf::tokenField(); ?>
            <div class="mb-3">
                <label for="accion" class="form-label">Número de Acción</label>
                <input type="number" class="form-control" id="accion" name="accion" required placeholder="Ingrese su número de acción">
            </div>
            <div class="mb-3">
                <label for="cedula" class="form-label">Cédula de Identidad</label>
                <input type="text" class="form-control" id="cedula" name="cedula" required placeholder="V-12345678">
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Nombres y Apellidos">
            </div>
            
            <!-- Estado se define automáticamente como ACTIVO o PENDIENTE, oculto al usuario -->
            <input type="hidden" name="estado" value="ACTIVO">

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Registrarse</button>
                <a href="index.php" class="btn btn-outline-light">Volver al Login</a>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
