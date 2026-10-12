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

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-users-cog text-primary mr-2"></i>Gestión de Usuarios
    </h1>
    <a href="index.php?action=usuarios_create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50 mr-1"></i> Nuevo Usuario
    </a>
</div>

<!-- Alert Messages -->
<?php 
$flash_success = flash('success');
$flash_error = flash('error');
$flash_warning = flash('warning');

if($flash_success || $flash_error || $flash_warning || isset($error)): 
?>
    <div class="alert alert-<?php echo ($flash_success) ? 'success' : (($flash_warning) ? 'warning' : 'danger'); ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas <?php echo ($flash_success) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
        <?php 
            echo $flash_success ?? $flash_error ?? $flash_warning ?? $error; 
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'delete_failed') echo ' Error al eliminar el usuario.';
                if ($_GET['error'] == 'cannot_delete_self') echo ' No puedes eliminar tu propio usuario.';
            }
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable" cellspacing="0" style="width: auto; min-width: 1000px;">
                <thead class="bg-light text-dark">
                    <tr>
                        <th class="border-top-0 py-3 pl-4 text-left">Usuario</th>
                        <th class="border-top-0 py-3 pl-4 text-left">Email</th>
                        <th class="border-top-0 py-3 text-center" style="width: 150px;">Tipo</th>
                        <th class="border-top-0 py-3 text-center" style="width: 150px;">Puesto</th>
                        <th class="border-top-0 py-3 text-center" style="width: 120px;">Huella</th>
                        <th class="border-top-0 pr-4 py-3 text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $user): ?>
                    <tr>
                        <td class="align-middle pl-4">
                            <strong><?php echo htmlspecialchars($user['usuario']); ?></strong>
                            <?php if ($user['usuario'] === $_SESSION['usuario']): ?>
                                <span class="badge badge-info ml-1">Tú</span>
                            <?php endif; ?>
                            <?php if (isset($user['bloqueado']) && $user['bloqueado'] == 1): ?>
                                <span class="badge badge-danger ml-1">Bloqueado</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle pl-4 text-muted small"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                        <td class="align-middle text-center">
                            <span class="badge badge-<?php echo $user['tipousuario'] == 'Administrador' ? 'danger' : ($user['tipousuario'] == 'Operador' ? 'primary' : 'secondary'); ?> px-3 py-1">
                                <?php echo htmlspecialchars($user['tipousuario']); ?>
                            </span>
                        </td>
                        <td class="align-middle text-center"><small><?php echo htmlspecialchars($user['puestotrabajo'] ?? 'N/A'); ?></small></td>
                        <td class="align-middle text-center">
                            <?php if ($user['tiene_huella']): ?>
                                <span class="badge badge-success px-2 py-1 shadow-sm" title="Huella registrada">
                                    <i class="fas fa-fingerprint"></i> Reg.
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-2 py-1 shadow-sm" title="Sin huella">
                                    <i class="fas fa-times-circle"></i> No Reg.
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle text-center pr-4">
                            <div class="btn-group shadow-sm" role="group">
                                <a href="index.php?action=usuarios_edit&usuario=<?php echo urlencode($user['usuario']); ?>" 
                                   class="btn btn-sm btn-light border" title="Editar Usuario">
                                    <i class="fas fa-edit text-primary"></i>
                                </a>
                                <a href="index.php?action=usuarios_change_password&usuario=<?php echo urlencode($user['usuario']); ?>" 
                                   class="btn btn-sm btn-light border" title="Cambiar Contraseña">
                                    <i class="fas fa-key text-warning"></i>
                                </a>
                                
                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-light border dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h text-gray-600"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="btnGroupDrop1">
                                        <div class="dropdown-header">Más Opciones:</div>
                                        
                                        <?php if ($user['tiene_huella']): ?>
                                            <a class="dropdown-item text-success" href="#" onclick="abrirModalHuella('<?php echo htmlspecialchars($user['usuario']); ?>', '<?php echo htmlspecialchars($user['usuario']); ?>')">
                                                <i class="fas fa-fingerprint fa-sm fa-fw mr-2"></i> Actualizar Huella
                                            </a>
                                            <a class="dropdown-item text-danger" href="#" onclick="eliminarHuella('<?php echo htmlspecialchars($user['usuario']); ?>', '<?php echo htmlspecialchars($user['usuario']); ?>')">
                                                <i class="fas fa-fingerprint-slash fa-sm fa-fw mr-2"></i> Eliminar Huella
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item text-info" href="#" onclick="abrirModalHuella('<?php echo htmlspecialchars($user['usuario']); ?>', '<?php echo htmlspecialchars($user['usuario']); ?>')">
                                                <i class="fas fa-fingerprint fa-sm fa-fw mr-2"></i> Registrar Huella
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($user['usuario'] !== $_SESSION['usuario']): ?>
                                            <div class="dropdown-divider"></div>
                                            <?php if (isset($user['bloqueado']) && $user['bloqueado'] == 1): ?>
                                                <form action="index.php?action=usuarios_toggle_block" method="POST" class="m-0 p-0">
                                                    <?php echo Csrf::tokenField(); ?>
                                                    <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>">
                                                    <input type="hidden" name="status" value="0">
                                                    <button type="submit" class="dropdown-item text-success bg-transparent border-0 w-100 text-left">
                                                        <i class="fas fa-unlock fa-sm fa-fw mr-2"></i> Desbloquear Usuario
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="index.php?action=usuarios_toggle_block" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de bloquear a este usuario?');">
                                                    <?php echo Csrf::tokenField(); ?>
                                                    <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>">
                                                    <input type="hidden" name="status" value="1">
                                                    <button type="submit" class="dropdown-item text-secondary bg-transparent border-0 w-100 text-left">
                                                        <i class="fas fa-ban fa-sm fa-fw mr-2"></i> Bloquear Usuario
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <div class="dropdown-divider"></div>
                                            <form action="index.php?action=usuarios_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar permanentemente al usuario <?php echo htmlspecialchars($user['usuario']); ?>?');">
                                                <?php echo Csrf::tokenField(); ?>
                                                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>">
                                                <button type="submit" class="dropdown-item text-danger font-weight-bold bg-transparent border-0 w-100 text-left">
                                                    <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Registro
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3 border-top-0">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <small class="text-muted">Total de registros: <strong><?php echo $total_registros; ?></strong></small>
            </div>
            <div class="col-sm-6 text-right">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=usuarios&page=1" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=usuarios&page=<?php echo $pagina - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $pagina - 2);
                        $end = max(1, min($total_paginas, $pagina + 2));
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?action=usuarios&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=usuarios&page=<?php echo $pagina + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($pagina >= $total_paginas || $total_paginas <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?action=usuarios&page=<?php echo max(1, $total_paginas); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Captura Huella -->
<div class="modal fade" id="modalHuella" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Huella - <span id="nombreUsuarioHuella"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="estadoHuella" class="alert alert-info">
                    El sistema capturará 3 muestras de su huella para garantizar la mejor calidad. Presione "Iniciar Captura" cuando esté listo.
                </div>
                <div class="my-3">
                    <i class="fas fa-fingerprint" style="font-size: 4rem; color: #6c757d;" id="iconoHuella"></i>
                </div>
                <div class="progress mb-3 d-none" id="progresoHuella">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="iniciarCaptura()">Iniciar Captura</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cedulaActual = '';

    function abrirModalHuella(cedula, nombre) {
        cedulaActual = cedula;
        document.getElementById('nombreUsuarioHuella').textContent = nombre;
        document.getElementById('estadoHuella').className = 'alert alert-info';
        document.getElementById('estadoHuella').textContent = 'El sistema capturará 3 muestras de su huella para garantizar la mejor calidad. Presione "Iniciar Captura" cuando esté listo.';
        document.getElementById('iconoHuella').style.color = '#6c757d';
        
        $('#modalHuella').modal('show');
    }

    async function iniciarCaptura() {
        const estado = document.getElementById('estadoHuella');
        const icono = document.getElementById('iconoHuella');
        const progreso = document.getElementById('progresoHuella');
        const btnIniciar = event.target;
        
        // Deshabilitar botón durante captura
        btnIniciar.disabled = true;
        
        estado.textContent = '⏳ Capturando 3 muestras... Coloque su dedo en el sensor y manténgalo presionado firmemente. Proceso: 3-5 segundos.';
        estado.className = 'alert alert-warning';
        progreso.classList.remove('d-none');
        icono.style.color = '#ffc107';
        
        console.log('[Huella] Iniciando captura para cédula:', cedulaActual);
        
        try {
            // 1. Capturar desde Bridge C++
            console.log('[Huella] Conectando al bridge en http://localhost:8888/capture...');
            const startTime = Date.now();
            
            const driver = '<?php echo $huellaConfig["driver"]; ?>';
            const ip = '<?php echo $huellaConfig["hik_ip"]; ?>';
            const port = '<?php echo $huellaConfig["hik_port"]; ?>';
            const user = '<?php echo $huellaConfig["hik_user"]; ?>';
            const pass = '<?php echo $huellaConfig["hik_pass"]; ?>';

            const url = `http://localhost:8888/capture?driver=${driver}&ip=${ip}&port=${port}&user=${user}&pass=${pass}`;
            
            const response = await fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const elapsed = ((Date.now() - startTime) / 1000).toFixed(2);
            console.log(`[Huella] Respuesta recibida en ${elapsed}s`);

            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('[Huella] Datos recibidos:', data);
            
            if (data.status === 'success') {
                estado.textContent = `✓ Huella capturada con éxito (Calidad: ${data.quality}/100, Tiempo: ${elapsed}s). Guardando en base de datos...`;
                estado.className = 'alert alert-info';
                icono.style.color = '#28a745';
                
                console.log('[Huella] Template capturado, longitud:', data.template.length);
                
                // 2. Guardar en PHP
                console.log('[Huella] Guardando en base de datos...');
                const saveResponse = await fetch('index.php?action=huella_save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cedula=${encodeURIComponent(cedulaActual)}&template=${encodeURIComponent(data.template)}`
                });
                
                const saveData = await saveResponse.json();
                console.log('[Huella] Respuesta del guardado:', saveData);
                
                if (saveData.success) {
                    estado.textContent = '✓ ¡Huella guardada exitosamente en la base de datos!';
                    estado.className = 'alert alert-success';
                    icono.style.color = '#28a745';
                    
                    console.log('[Huella] Proceso completado exitosamente');
                    
                    setTimeout(() => {
                        $('#modalHuella').modal('hide');
                        location.reload(); // Recargar para actualizar el estado
                    }, 2000);
                } else {
                    throw new Error(saveData.message || 'Error al guardar en base de datos');
                }
            } else {
                throw new Error(data.message || 'Error desconocido en la captura');
            }
        } catch (error) {
            console.error('[Huella] Error:', error);
            
            let errorMsg = error.message;
            
            // Mensajes de error más amigables
            if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
                errorMsg = 'No se pudo conectar al bridge. Asegúrese de que run_cpp_bridge.bat esté ejecutándose.';
            } else if (error.message.includes('timeout')) {
                errorMsg = 'Tiempo de espera agotado. Intente mantener el dedo presionado por más tiempo.';
            } else if (error.message.includes('JSON')) {
                errorMsg = 'Respuesta inválida del servidor. Revise los logs del bridge.';
            }
            
            estado.textContent = '✗ Error: ' + errorMsg;
            estado.className = 'alert alert-danger';
            icono.style.color = '#dc3545';
        } finally {
            progreso.classList.add('d-none');
            btnIniciar.disabled = false;
        }
    }
    
    async function eliminarHuella(cedula, nombre) {
        if (!confirm(`¿Está seguro de eliminar la huella de ${nombre}?`)) {
            return;
        }
        
        console.log('[Huella] Eliminando huella para cédula:', cedula);
        
        try {
            const token = document.querySelector('input[name="csrf_token"]').value;
            const response = await fetch('index.php?action=huella_delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cedula=${encodeURIComponent(cedula)}&csrf_token=${encodeURIComponent(token)}`
            });
            
            const data = await response.json();
            console.log('[Huella] Respuesta:', data);
            
            if (data.success) {
                alert('Huella eliminada exitosamente');
                location.reload(); // Recargar la página para actualizar la tabla
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar la huella'));
            }
        } catch (error) {
            console.error('[Huella] Error:', error);
            alert('Error al eliminar la huella: ' + error.message);
        }
    }
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
