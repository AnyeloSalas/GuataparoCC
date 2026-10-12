<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
    header("Location: index.php?action=votaciones_postulados");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Postulado - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Sistema Guataparo</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Registrar Nuevo Postulado</h2>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mt-4">
            <div class="card-body">
                <form action="index.php?action=votaciones_crear_postulado" method="POST">
                    <?php echo Csrf::tokenField(); ?>
                    <div class="row">
                            <input type="hidden" name="tipomiembro" value="PRINCIPAL">


                        <div class="col-md-6 mb-3">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accion" class="form-label">Acción</label>
                            <input type="text" class="form-control" id="accion" name="accion">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profesion" class="form-label">Profesión</label>
                            <input type="text" class="form-control" id="profesion" name="profesion">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo_electronico" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                            <select class="form-select" id="nacionalidad" name="nacionalidad">
                                <option value="V">Venezolano</option>
                                <option value="E">Extranjero</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Propuestas</label>
                        <input type="text" class="form-control mb-2" name="pos1" placeholder="Propuesta 1">
                        <input type="text" class="form-control mb-2" name="pos2" placeholder="Propuesta 2">
                        <input type="text" class="form-control mb-2" name="pos3" placeholder="Propuesta 3">
                        <input type="text" class="form-control" name="pos4" placeholder="Propuesta 4">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Guardar Postulado</button>
                        <a href="index.php?action=votaciones_postulados" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
