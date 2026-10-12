<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] !== 'Administrador') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Configuración - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Configuración</h4>
                    </div>
                    <div class="card-body">
                        <form action="index.php?action=configuracion_create" method="POST">
                            <?php echo Csrf::tokenField(); ?>
                            <div class="mb-3">
                                <label for="clave" class="form-label">Clave <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="clave" 
                                       name="clave" 
                                       required
                                       pattern="[a-z_]+"
                                       placeholder="ejemplo: mi_configuracion">
                                <small class="text-muted">Solo letras minúsculas y guiones bajos. Ejemplo: sistema_nombre</small>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="descripcion" 
                                       name="descripcion" 
                                       required
                                       placeholder="Descripción legible de la configuración">
                                <small class="text-muted">Nombre descriptivo que aparecerá en el formulario</small>
                            </div>

                            <div class="mb-3">
                                <label for="valor" class="form-label">Valor Inicial</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="valor" 
                                       name="valor" 
                                       placeholder="Valor por defecto">
                            </div>

                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Campo</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="text">Texto</option>
                                    <option value="textarea">Área de Texto</option>
                                    <option value="number">Número</option>
                                    <option value="email">Email</option>
                                    <option value="color">Color</option>
                                    <option value="boolean">Sí/No (Boolean)</option>
                                </select>
                                <small class="text-muted">Define el tipo de campo que se mostrará en el formulario</small>
                            </div>

                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="general">General</option>
                                    <option value="apariencia">Apariencia</option>
                                    <option value="seguridad">Seguridad</option>
                                    <option value="notificaciones">Notificaciones</option>
                                    <option value="votacion">Votación</option>
                                </select>
                                <small class="text-muted">Agrupa la configuración en una categoría específica</small>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Asegúrate de que la clave sea única y descriptiva. Una vez creada, la clave no se puede modificar fácilmente.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Crear Configuración
                                </button>
                                <a href="index.php?action=configuracion" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-question-circle"></i> Ayuda</h5>
                    </div>
                    <div class="card-body">
                        <h6>Tipos de Campo:</h6>
                        <ul>
                            <li><strong>Texto:</strong> Campo de texto simple para valores cortos</li>
                            <li><strong>Área de Texto:</strong> Campo de texto multilínea para valores largos</li>
                            <li><strong>Número:</strong> Solo acepta valores numéricos</li>
                            <li><strong>Email:</strong> Valida formato de correo electrónico</li>
                            <li><strong>Color:</strong> Selector de color visual</li>
                            <li><strong>Boolean:</strong> Selector Sí/No para valores verdadero/falso</li>
                        </ul>

                        <h6 class="mt-3">Categorías:</h6>
                        <ul>
                            <li><strong>General:</strong> Información básica del sistema</li>
                            <li><strong>Apariencia:</strong> Colores, logos y diseño</li>
                            <li><strong>Seguridad:</strong> Configuraciones de seguridad y acceso</li>
                            <li><strong>Notificaciones:</strong> Configuración de alertas y emails</li>
                            <li><strong>Votación:</strong> Parámetros del sistema de votación</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
</body>
</html>
