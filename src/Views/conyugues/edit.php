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
        <h1 class="h3 mb-0 text-gray-800">Editar Familiar</h1>
        <a href="index.php?action=conyugues" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al listado
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-primary">
                    <h6 class="m-0 font-weight-bold text-primary">Modificar Información del Familiar</h6>
                    <span class="badge badge-primary px-3 py-2">Acción #<?php echo htmlspecialchars($this->conyugue->accionconyugue); ?></span>
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

                    <form action="index.php?action=conyugues_update" method="POST">
                        <?php echo Csrf::tokenField(); ?>
                        <input type="hidden" name="accionconyugue" value="<?php echo htmlspecialchars($this->conyugue->accionconyugue); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold text-muted">Acción Asociada</label>
                                    <div class="p-3 bg-light rounded border-left-secondary">
                                        <i class="fas fa-link mr-2 text-secondary"></i>
                                        <strong><?php echo htmlspecialchars($this->conyugue->accionconyugue); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cedulaconyugue" class="font-weight-bold">Cédula de Identidad <span class="text-danger">*</span></label>
                                    <div class="row no-gutters shadow-sm">
                                        <div class="col-3">
                                            <?php 
                                            // La nacionalidad ya viene cargada en el modelo como nacionalidad_codigo
                                            $nac_actual = $this->conyugue->nacionalidad_codigo ?? 'V';
                                            $num_actual = $this->conyugue->cedulaconyugue;
                                            ?>
                                            <select class="form-control font-weight-bold" name="nacionalidad" id="nacionalidad" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <?php foreach ($nacionalidades as $nac): ?>
                                                    <option value="<?php echo $nac['codigo']; ?>" <?php echo ($nac_actual == $nac['codigo']) ? 'selected' : ''; ?>>
                                                        <?php echo $nac['codigo']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-9">
                                            <input type="text" class="form-control font-weight-bold" id="cedulaconyugue" name="cedulaconyugue" 
                                                   value="<?php echo htmlspecialchars($num_actual); ?>" required style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light p-4 mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="primer_nombre" class="small font-weight-bold text-primary">Primer Nombre *</label>
                                        <input type="text" class="form-control shadow-sm border-0" id="primer_nombre" name="primer_nombre" 
                                               value="<?php echo htmlspecialchars($this->conyugue->primer_nombre ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="segundo_nombre" class="small font-weight-bold text-muted">Segundo Nombre</label>
                                        <input type="text" class="form-control shadow-sm border-0" id="segundo_nombre" name="segundo_nombre" 
                                               value="<?php echo htmlspecialchars($this->conyugue->segundo_nombre ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6 border-left">
                                    <div class="form-group pl-md-3">
                                        <label for="primer_apellido" class="small font-weight-bold text-primary">Primer Apellido *</label>
                                        <input type="text" class="form-control shadow-sm border-0" id="primer_apellido" name="primer_apellido" 
                                               value="<?php echo htmlspecialchars($this->conyugue->primer_apellido ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group pl-md-3">
                                        <label for="segundo_apellido" class="small font-weight-bold text-muted">Segundo Apellido</label>
                                        <input type="text" class="form-control shadow-sm border-0" id="segundo_apellido" name="segundo_apellido" 
                                               value="<?php echo htmlspecialchars($this->conyugue->segundo_apellido ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_relacion" class="font-weight-bold">Parentesco / Relación <span class="text-danger">*</span></label>
                                    <select class="form-control border-left-info shadow-sm" id="tipo_relacion" name="tipo_relacion" required>
                                        <?php if (!empty($tipos_relacion)): ?>
                                            <?php foreach($tipos_relacion as $tipo): ?>
                                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo ($this->conyugue->tipo_relacion == $tipo) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tipo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado_civil" class="font-weight-bold">Estado Civil <span class="text-danger">*</span></label>
                                    <select class="form-control shadow-sm" id="estado_civil" name="estado_civil" required>
                                        <option value="Casado" <?php echo ($this->conyugue->estado_civil == 'Casado' || $this->conyugue->estado_civil == 'Casada') ? 'selected' : ''; ?>>Casado/a</option>
                                        <option value="Soltero" <?php echo ($this->conyugue->estado_civil == 'Soltero' || $this->conyugue->estado_civil == 'Soltera') ? 'selected' : ''; ?>>Soltero/a</option>
                                        <option value="Divorciado" <?php echo ($this->conyugue->estado_civil == 'Divorciado' || $this->conyugue->estado_civil == 'Divorciada') ? 'selected' : ''; ?>>Divorciado/a</option>
                                        <option value="Viudo" <?php echo ($this->conyugue->estado_civil == 'Viudo' || $this->conyugue->estado_civil == 'Viuda') ? 'selected' : ''; ?>>Viudo/a</option>
                                        <option value="Concubinato" <?php echo ($this->conyugue->estado_civil == 'Concubinato') ? 'selected' : ''; ?>>Concubinato</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 d-flex justify-content-between align-items-center border-top pt-4">
                            <a href="index.php?action=conyugues" class="text-danger small font-weight-bold">
                                <i class="fas fa-trash-alt mr-1"></i> Descartar cambios
                            </a>
                            <div class="actions">
                                <a href="index.php?action=conyugues" class="btn btn-secondary shadow-sm mr-2 px-4">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary shadow-sm px-5">
                                    <i class="fas fa-sync-alt mr-1"></i> Actualizar Familiar
                                </button>
                            </div>
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
