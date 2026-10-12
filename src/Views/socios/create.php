<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// La verificación de sesión ya se hace en el controlador/index, pero por seguridad:
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
        <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Socio</h1>
        <a href="index.php?action=socios" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al listado
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Datos del Socio</h6>
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

                    <div class="alert alert-info border-left-info shadow-sm mb-4">
                        <i class="fas fa-info-circle fa-fw"></i> 
                        <strong>Requisito de Admisión:</strong> 
                        Solo se permite el registro de personas que hayan participado en un proceso de votación como postulados y cuya sumatoria de votos sea favorable (Mayoría de 'ACEPTADO').
                    </div>

                    <?php if(!empty($admitidos)): ?>
                    <div class="card bg-light mb-4 border-left-success">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success mb-3"><i class="fas fa-user-check"></i> Candidatos Admitidos (Pendientes por Registro)</h6>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover bg-white mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($admitidos as $adm): ?>
                                        <tr>
                                            <td class="align-middle py-2 pl-3">
                                                <div class="font-weight-bold"><?php echo htmlspecialchars($adm['nombre']); ?></div>
                                                <span class="badge badge-secondary small text-uppercase"><?php echo htmlspecialchars($adm['tipomiembro']); ?></span>
                                            </td>
                                            <td class="align-middle"><?php echo htmlspecialchars($adm['cedula']); ?></td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-success btn-transferir" 
                                                        data-cedula="<?php echo htmlspecialchars($adm['cedula']); ?>">
                                                    <i class="fas fa-share"></i> Transferir
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form action="index.php?action=socios_create" method="POST" id="formSocio">
                        <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="accion"># Accion Socio <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="accion" name="accion" required
                                           value="<?php echo htmlspecialchars(old('accion') ?: ($proxima_accion ?? '')); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="nacionalidad">Nac. <span class="text-danger">*</span></label>
                                    <select class="form-control" id="nacionalidad" name="nacionalidad">
                                        <option value="V" <?php echo old('nacionalidad', 'V') == 'V' ? 'selected' : ''; ?>>V</option>
                                        <option value="E" <?php echo old('nacionalidad') == 'E' ? 'selected' : ''; ?>>E</option>
                                        <option value="P" <?php echo old('nacionalidad') == 'P' ? 'selected' : ''; ?>>P</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cedula">Cédula de Identidad <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="cedula" name="cedula" required
                                               value="<?php echo htmlspecialchars(old('cedula')); ?>" 
                                               placeholder="12345678"
                                               pattern="[0-9]{5,9}" title="Solo números, de 5 a 9 dígitos">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="btnBuscarPostulado">
                                                <i class="fas fa-search"></i> Cargar Datos
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Solo números (sin V- ni guión).</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" id="estado" name="estado">
                                        <option value="ACTIVO" <?php echo old('estado') == 'ACTIVO' ? 'selected' : ''; ?>>ACTIVO</option>
                                        <option value="INACTIVO" <?php echo old('estado') == 'INACTIVO' ? 'selected' : ''; ?>>INACTIVO</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_nombre">Primer Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" required
                                           value="<?php echo htmlspecialchars(old('primer_nombre')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_nombre">Segundo Nombre</label>
                                    <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre"
                                           value="<?php echo htmlspecialchars(old('segundo_nombre')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primer_apellido">Primer Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" required
                                           value="<?php echo htmlspecialchars(old('primer_apellido')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="segundo_apellido">Segundo Apellido</label>
                                    <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido"
                                           value="<?php echo htmlspecialchars(old('segundo_apellido')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars(old('email')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono_movil">Teléfono Móvil</label>
                                    <input type="text" class="form-control" id="telefono_movil" name="telefono_movil"
                                           value="<?php echo htmlspecialchars(old('telefono_movil')); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sexo">Sexo</label>
                                    <select name="sexo" id="sexo" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="M" <?php echo old('sexo') == 'M' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="F" <?php echo old('sexo') == 'F' ? 'selected' : ''; ?>>Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                           value="<?php echo htmlspecialchars(old('fecha_nacimiento')); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="profesion_id">Profesión</label>
                                    <select name="profesion_id" id="profesion_id" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <?php if(isset($profesiones)): ?>
                                            <?php foreach($profesiones as $p): ?>
                                                <option value="<?php echo $p['id']; ?>" <?php echo old('profesion_id') == $p['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lugar_trabajo">Lugar de Trabajo</label>
                            <input type="text" class="form-control" id="lugar_trabajo" name="lugar_trabajo"
                                   value="<?php echo htmlspecialchars(old('lugar_trabajo')); ?>">
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-end">
                            <a href="index.php?action=socios" class="btn btn-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save fa-sm"></i> Guardar y Transferir Datos
                            </button>
                        </div>
                    </form>

                    <script>
                    // Función para cargar datos desde un objeto o API
                    function cargarDatosPostulado(cedulaInput) {
                        // Limpiar: extraer solo números
                        const soloNumeros = cedulaInput.replace(/[^0-9]/g, '');
                        document.getElementById('cedula').value = soloNumeros;
                        
                        // Si tenía prefijo V/E/P, setear el selector de nacionalidad
                        const prefixMatch = cedulaInput.match(/^([VEP])/i);
                        if (prefixMatch) {
                            document.getElementById('nacionalidad').value = prefixMatch[1].toUpperCase();
                        }
                        
                        document.getElementById('btnBuscarPostulado').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
                        document.getElementById('btnBuscarPostulado').disabled = true;

                        fetch(`index.php?action=socios_buscar_postulado&cedula=${soloNumeros}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const p = data.data;
                                    document.getElementById('primer_nombre').value = p.primer_nombre || '';
                                    document.getElementById('segundo_nombre').value = p.segundo_nombre || '';
                                    document.getElementById('primer_apellido').value = p.primer_apellido || '';
                                    document.getElementById('segundo_apellido').value = p.segundo_apellido || '';
                                    document.getElementById('lugar_trabajo').value = p.lugar_trabajo || '';
                                    document.getElementById('email').value = p.email || '';
                                    document.getElementById('telefono_movil').value = p.telefono_movil || '';
                                    document.getElementById('sexo').value = p.sexo || '';
                                    document.getElementById('fecha_nacimiento').value = p.fecha_nacimiento || '';
                                    
                                    if (p.profesion_id) {
                                        document.getElementById('profesion_id').value = p.profesion_id;
                                    }
                                    
                                    // Setear nacionalidad si viene en la respuesta
                                    if (p.nacionalidad) {
                                        document.getElementById('nacionalidad').value = p.nacionalidad;
                                    }
                                    
                                    // Feedback visual suave en vez de alert si viene de la lista
                                    console.log('Datos cargados:', data.votacion);
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error al buscar el postulado');
                            })
                            .finally(() => {
                                document.getElementById('btnBuscarPostulado').innerHTML = '<i class="fas fa-search"></i> Cargar Datos';
                                document.getElementById('btnBuscarPostulado').disabled = false;
                            });
                    }

                    // Botón manual de búsqueda
                    document.getElementById('btnBuscarPostulado').addEventListener('click', function() {
                        const cedulaCompleta = document.getElementById('cedula').value;
                        
                        if (!cedulaCompleta) {
                            alert('Por favor ingrese la cédula');
                            return;
                        }

                        cargarDatosPostulado(cedulaCompleta);
                    });

                    // Botones de transferencia de la lista de admitidos
                    document.querySelectorAll('.btn-transferir').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const cedula = this.getAttribute('data-cedula');
                            cargarDatosPostulado(cedula);
                            
                            // Hacer scroll suave al formulario
                            document.getElementById('formSocio').scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    });
                    
                    // Auto-limpiar: si el usuario pega V-12345, quitar el prefijo
                    document.getElementById('cedula').addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
