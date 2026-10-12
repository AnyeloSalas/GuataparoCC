<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
    header("Location: index.php?action=postulados");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-edit text-primary mr-2"></i>
        Editar Postulado
    </h1>
    <a href="index.php?action=postulados" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a la lista
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-edit mr-2"></i>Modificar Datos del Postulado
             <span class="badge badge-secondary ml-2">
                Cédula: 
                <span class="badge badge-light text-primary"><?php echo htmlspecialchars($postulado->nac_codigo ?? 'V'); ?></span>
                <?php echo htmlspecialchars($postulado->cedula); ?>
            </span>
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?action=postulados_edit&cedula=<?php echo urlencode($postulado->cedula); ?>" class="needs-validation" novalidate enctype="multipart/form-data">
            <?php echo Csrf::tokenField(); ?>
            
            <div class="row">
                <!-- Información Personal -->
                <div class="col-lg-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user mr-2"></i>Información Personal
                    </h5>
                    
                    <input type="hidden" name="tipomiembro" value="PRINCIPAL">

                    <div class="form-group">
                        <label for="nacionalidad" class="font-weight-bold">Nacionalidad</label>
                        <select class="form-control" id="nacionalidad" name="nacionalidad_id">
                            <?php foreach ($nacionalidades as $nac): ?>
                                <option value="<?php echo $nac['id']; ?>" <?php echo ($postulado->nacionalidad_id == $nac['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nac['nombre']); ?> (<?php echo htmlspecialchars($nac['codigo']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cedula" class="font-weight-bold">Cédula de Identidad</label>
                        <input type="text" class="form-control bg-light" id="cedula" 
                               value="<?php echo htmlspecialchars($postulado->cedula); ?>" 
                               readonly disabled>
                        <small class="form-text text-muted">La cédula no puede ser modificada.</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primer_nombre" class="font-weight-bold">Primer Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" 
                                       required maxlength="100" placeholder="Primer Nombre"
                                       value="<?php echo htmlspecialchars($postulado->primer_nombre ?? ''); ?>">
                                <div class="invalid-feedback">Ingrese el primer nombre.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="segundo_nombre" class="font-weight-bold">Segundo Nombre</label>
                                <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre" 
                                       maxlength="100" placeholder="Segundo Nombre"
                                       value="<?php echo htmlspecialchars($postulado->segundo_nombre ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primer_apellido" class="font-weight-bold">Primer Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" 
                                       required maxlength="100" placeholder="Primer Apellido"
                                       value="<?php echo htmlspecialchars($postulado->primer_apellido ?? ''); ?>">
                                <div class="invalid-feedback">Ingrese el primer apellido.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="segundo_apellido" class="font-weight-bold">Segundo Apellido</label>
                                <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido" 
                                       maxlength="100" placeholder="Segundo Apellido"
                                       value="<?php echo htmlspecialchars($postulado->segundo_apellido ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información Profesional -->
                <div class="col-lg-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-briefcase mr-2"></i>Información Profesional
                    </h5>
                    
                    <div class="form-group">
                        <label for="profesion" class="font-weight-bold">Profesión</label>
                        <select class="form-control select2" id="profesion" name="profesion">
                            <option value="">Seleccione...</option>
                            <?php foreach($profesiones as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['nombre']); ?>" <?php echo ($postulado->profesion == $p['nombre']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo_electronico" class="font-weight-bold">Correo Electrónico</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" 
                                   maxlength="150" placeholder="ejemplo@correo.com"
                                   value="<?php echo htmlspecialchars($postulado->correo_electronico ?? ''); ?>">
                        </div>
                        <div class="invalid-feedback">Ingrese un formato de correo válido.</div>
                    </div>

                    <div class="form-group">
                        <label for="foto" class="font-weight-bold">Foto del Postulado</label>
                        <div class="mb-3 text-center">
                            <img id="currentPhoto" src="<?php echo !empty($postulado->foto) ? 'data:image/jpeg;base64,' . base64_encode($postulado->foto) : ''; ?>" 
                                 alt="Foto del postulado" 
                                 class="img-thumbnail" 
                                 style="max-height: 200px; <?php echo empty($postulado->foto) ? 'display:none;' : ''; ?>">
                            <p class="text-muted small mt-1" id="photoLabel"><?php echo !empty($postulado->foto) ? 'Foto actual' : ''; ?></p>
                        </div>

                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*" onchange="previewEditImage(this)">
                            <label class="custom-file-label" for="foto" data-browse="Buscar">Seleccionar nueva imagen...</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB. Dejar en blanco para mantener la actual.</small>
                    </div>
                    
                    <script>
                    function previewEditImage(input) {
                        var preview = document.getElementById('currentPhoto');
                        var label = document.getElementById('photoLabel');
                        
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                                label.innerHTML = 'Nueva selección';
                            }
                            
                            reader.readAsDataURL(input.files[0]);
                            
                            // Update filename label
                            var fileName = input.value.split('\\').pop();
                            input.nextElementSibling.innerHTML = fileName;
                        }
                    }
                    </script>
                    

                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Referido Por -->
            <h5 class="text-primary mb-3">
                <i class="fas fa-handshake mr-2"></i>Referido Por
            </h5>
            <p class="text-muted mb-3">Indique si el postulado es referido por un Socio o una Empresa.</p>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipo_referencia" class="font-weight-bold">Tipo de Referencia <span class="text-danger">*</span></label>
                        <select class="form-control" id="tipo_referencia" name="tipo_referencia" required>
                            <option value="">Seleccione...</option>
                            <option value="SOCIO" <?php echo ($postulado->tipo_referencia == 'SOCIO') ? 'selected' : ''; ?>>Socio</option>
                            <option value="EMPRESA" <?php echo ($postulado->tipo_referencia == 'EMPRESA') ? 'selected' : ''; ?>>Empresa</option>
                        </select>
                        <div class="invalid-feedback">Seleccione el tipo de referencia.</div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Select para SOCIO -->
                    <div class="form-group" id="div_socio" style="display:none;">
                        <label for="ref_socio" class="font-weight-bold">Seleccione Socio <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="ref_socio" name="referencia_id" disabled>
                            <option value="">Buscar Socio...</option>
                            <?php foreach($socios as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ($postulado->tipo_referencia == 'SOCIO' && $postulado->referencia_id == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['display_text']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione un socio.</div>
                    </div>

                    <!-- Select para EMPRESA -->
                    <div class="form-group" id="div_empresa" style="display:none;">
                        <label for="ref_empresa" class="font-weight-bold">Seleccione Empresa <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="ref_empresa" name="referencia_id" disabled>
                            <option value="">Buscar Empresa...</option>
                            <?php foreach($empresas as $e): ?>
                                <option value="<?php echo $e['id']; ?>" <?php echo ($postulado->tipo_referencia == 'EMPRESA' && $postulado->referencia_id == $e['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['display_text']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Seleccione una empresa.</div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tipoSelect = document.getElementById('tipo_referencia');
                const divSocio = document.getElementById('div_socio');
                const divEmpresa = document.getElementById('div_empresa');
                const selectSocio = document.getElementById('ref_socio');
                const selectEmpresa = document.getElementById('ref_empresa');

                function toggleReferencia() {
                    const tipo = tipoSelect.value;
                    
                    if (tipo === 'SOCIO') {
                        divSocio.style.display = 'block';
                        selectSocio.disabled = false;
                        selectSocio.required = true;
                        
                        divEmpresa.style.display = 'none';
                        selectEmpresa.disabled = true;
                        selectEmpresa.required = false;
                    } else if (tipo === 'EMPRESA') {
                        divSocio.style.display = 'none';
                        selectSocio.disabled = true;
                        selectSocio.required = false;
                        
                        divEmpresa.style.display = 'block';
                        selectEmpresa.disabled = false;
                        selectEmpresa.required = true;
                    } else {
                        divSocio.style.display = 'none';
                        selectSocio.disabled = true;
                        selectSocio.required = false;
                        
                        divEmpresa.style.display = 'none';
                        selectEmpresa.disabled = true;
                        selectEmpresa.required = false;
                    }
                }

                tipoSelect.addEventListener('change', toggleReferencia);
                // Run on load to set initial state
                toggleReferencia();
            });
            </script>
            
            <hr class="my-4">
            
            <div class="d-flex justify-content-between">
                <a href="index.php?action=postulados" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <div>
                    <button type="submit" form="form-delete-postulado" class="btn btn-outline-danger mr-2"
                       onclick="return confirm('¿Está seguro de eliminar este postulado?\n\n<?php echo htmlspecialchars(addslashes($postulado->nombre)); ?>\n\nEsta acción no se puede deshacer.')">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
        <form id="form-delete-postulado" action="index.php?action=postulados_delete" method="POST" style="display:none;">
            <?php echo Csrf::tokenField(); ?>
            <input type="hidden" name="cedula" value="<?php echo htmlspecialchars($postulado->cedula); ?>">
        </form>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
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

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
