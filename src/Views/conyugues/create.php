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
        <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Familiar</h1>
        <a href="index.php?action=conyugues" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al listado
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Datos del Familiar</h6>
                    <i class="fas fa-user-friends text-primary"></i>
                </div>
                <div class="card-body">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info border-left-primary shadow-sm mb-4">
                        <i class="fas fa-info-circle fa-fw"></i> 
                        <strong>Información:</strong> Los familiares registrados en este módulo se asocian directamente a una acción de socio activo.
                    </div>

                    <form action="index.php?action=conyugues_create" method="POST">
                        <?php echo Csrf::tokenField(); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="accionconyugue" class="font-weight-bold text-dark">Socio Titular / Acción <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                        </div>
                                        <select class="form-control" id="accionconyugue" name="accionconyugue" required>
                                            <option value="">Seleccione el Socio...</option>
                                            <?php foreach($socios as $socio): ?>
                                                <option value="<?php echo $socio['accion']; ?>">
                                                    <?php echo $socio['accion'] . ' - ' . $socio['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                    <label for="cedulaconyugue" class="font-weight-bold text-dark">Cédula del Familiar <span class="text-danger">*</span></label>
                                    <div class="row no-gutters">
                                        <div class="col-4">
                                            <select class="form-control" name="nacionalidad" id="nacionalidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <?php foreach ($nacionalidades as $nac): ?>
                                                    <option value="<?php echo $nac['codigo']; ?>" <?php echo $nac['codigo'] == 'V' ? 'selected' : ''; ?>>
                                                        <?php echo $nac['codigo']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="form-control" id="cedulaconyugue" name="cedulaconyugue" required placeholder="Ej: 12345678" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 border-right">
                                <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-3 mt-2">Nombres</h6>
                                <div class="form-group">
                                    <label for="primer_nombre">Primer Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-user" id="primer_nombre" name="primer_nombre" required>
                                </div>
                                <div class="form-group">
                                    <label for="segundo_nombre">Segundo Nombre</label>
                                    <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-xs font-weight-bold text-primary text-uppercase mb-3 mt-2">Apellidos</h6>
                                <div class="form-group">
                                    <label for="primer_apellido">Primer Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" required>
                                </div>
                                <div class="form-group">
                                    <label for="segundo_apellido">Segundo Apellido</label>
                                    <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_relacion" class="font-weight-bold">Parentesco <span class="text-danger">*</span></label>
                                    <select class="form-control border-left-info" id="tipo_relacion" name="tipo_relacion" required>
                                        <?php if (!empty($tipos_relacion)): ?>
                                            <?php foreach($tipos_relacion as $tipo): ?>
                                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo $tipo == 'Esposa/o' ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tipo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="Esposa/o" selected>Esposa/o</option>
                                            <option value="Hijo/a">Hijo/a</option>
                                            <option value="Padre">Padre</option>
                                            <option value="Madre">Madre</option>
                                            <option value="Concubino/a">Concubino/a</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado_civil" class="font-weight-bold">Estado Civil <span class="text-danger">*</span></label>
                                    <select class="form-control" id="estado_civil" name="estado_civil" required>
                                        <option value="Casado">Casado/a</option>
                                        <option value="Soltero">Soltero/a</option>
                                        <option value="Divorciado">Divorciado/a</option>
                                        <option value="Viudo">Viudo/a</option>
                                        <option value="Concubinato">Concubinato</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-end">
                            <a href="index.php?action=conyugues" class="btn btn-light border mr-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success shadow-sm px-4">
                                <i class="fas fa-save"></i> Guardar Familiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
