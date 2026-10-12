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
    <h1 class="h3 mb-0 text-gray-800">Gestión de Socios</h1>
    <a href="index.php?action=socios_create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Socio
    </a>
</div>

<?php if($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($msg = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
            if($_GET['error'] == 'delete_failed') echo 'Error al eliminar el socio.';
            else echo htmlspecialchars($_GET['error']);
        ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Search Form -->
<div class="card shadow mb-4 border-left-primary">
    <div class="card-body">
        <form method="GET" action="index.php" class="form-row align-items-end">
            <input type="hidden" name="action" value="socios">
            
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold text-muted">Nombre o Apellido</label>
                <input type="text" class="form-control form-control-sm" name="search_nombre" 
                       placeholder="Nombre o Apellido..." value="<?php echo htmlspecialchars($search['nombre'] ?? ''); ?>">
            </div>

            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold text-muted">Cédula</label>
                <input type="text" class="form-control form-control-sm" name="search_cedula" 
                       placeholder="Cédula..." value="<?php echo htmlspecialchars($search['cedula'] ?? ''); ?>">
            </div>

            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold text-muted">Nro Acción</label>
                <input type="text" class="form-control form-control-sm" name="search_accion" 
                       placeholder="Acción..." value="<?php echo htmlspecialchars($search['accion'] ?? ''); ?>">
            </div>

            <div class="col-md-5 mb-2">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-search fa-sm mr-1"></i> Filtrar
                </button>
                <?php 
                    $has_filter = !empty($search['nombre']) || !empty($search['cedula']) || !empty($search['accion']) || !empty($search['keyword']);
                    if($has_filter): 
                ?>
                    <a href="index.php?action=socios" class="btn btn-secondary btn-sm px-3 ml-1">
                        <i class="fas fa-times fa-sm mr-1"></i> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table mr-2"></i>Lista de Socios</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                <div class="dropdown-header">Exportar:</div>
                <a class="dropdown-item" href="#"><i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-danger"></i> PDF (Próximamente)</a>
                <a class="dropdown-item" href="#"><i class="fas fa-file-excel fa-sm fa-fw mr-2 text-success"></i> Excel (Próximamente)</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
                <table class="table table-hover mb-0" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="border-top-0 py-3 text-center"># Accion Socio</th>
                            <th class="border-top-0 py-3">Cédula del Socio</th>
                            <th class="border-top-0 py-3">Socio (Nombre y Apellido)</th>
                            <th class="border-top-0 py-3 d-none d-md-table-cell">Correo</th>
                            <th class="border-top-0 py-3 d-none d-sm-table-cell text-center">Estado</th>
                            <th class="border-top-0 py-3 d-none d-lg-table-cell text-center">Huella</th>
                            <th class="border-top-0 pr-4 py-3 text-right" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php if (empty($socios)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No se encontraron socios.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($socios as $socio): ?>
                        <tr>
                            <td class="align-middle text-center">
                                <div class="badge badge-secondary py-2 px-3 shadow-sm">
                                    <i class="fas fa-id-badge mr-1"></i> <?php echo htmlspecialchars($socio['accion']); ?>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border text-primary"><?php echo htmlspecialchars($socio['nacionalidad_codigo'] ?? 'V'); ?></span>
                                <?php echo htmlspecialchars($socio['cedula']); ?>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($socio['nombre']); ?></td>
                            <td class="align-middle d-none d-md-table-cell small"><?php echo empty($socio['email']) ? '<span class="text-muted fst-italic">Sin correo</span>' : htmlspecialchars($socio['email']); ?></td>
                            <td class="align-middle d-none d-sm-table-cell text-center">
                                <div class="dropdown">
                                    <?php 
                                        $badge_class = 'secondary';
                                        if ($socio['estado'] == 'ACTIVO') $badge_class = 'success';
                                        if ($socio['estado'] == 'INACTIVO') $badge_class = 'secondary';
                                        if ($socio['estado'] == 'SUSPENDIDO') $badge_class = 'danger';
                                    ?>
                                    <button class="btn btn-sm btn-<?php echo $badge_class; ?> dropdown-toggle" type="button" 
                                            style="padding: 0.1rem 0.5rem; font-size: 0.75rem; min-width: 90px;"
                                            id="statusDropdown<?php echo $socio['accion']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?php echo htmlspecialchars($socio['estado']); ?>
                                    </button>
                                    <div class="dropdown-menu shadow animated--fade-in" aria-labelledby="statusDropdown<?php echo $socio['accion']; ?>">
                                        <h6 class="dropdown-header">Cambiar Estado:</h6>
                                        <form action="index.php?action=socios_status" method="POST" class="m-0 p-0">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="accion" value="<?php echo htmlspecialchars($socio['accion']); ?>">
                                            <input type="hidden" name="status" value="ACTIVO">
                                            <button type="submit" class="dropdown-item bg-transparent border-0 w-100 text-left">
                                                <i class="fas fa-check-circle text-success mr-2"></i> Activo
                                            </button>
                                        </form>
                                        <form action="index.php?action=socios_status" method="POST" class="m-0 p-0">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="accion" value="<?php echo htmlspecialchars($socio['accion']); ?>">
                                            <input type="hidden" name="status" value="INACTIVO">
                                            <button type="submit" class="dropdown-item bg-transparent border-0 w-100 text-left">
                                                <i class="fas fa-pause-circle text-secondary mr-2"></i> Inactivo
                                            </button>
                                        </form>
                                        <form action="index.php?action=socios_status" method="POST" class="m-0 p-0">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="accion" value="<?php echo htmlspecialchars($socio['accion']); ?>">
                                            <input type="hidden" name="status" value="SUSPENDIDO">
                                            <button type="submit" class="dropdown-item bg-transparent border-0 w-100 text-left">
                                                <i class="fas fa-exclamation-circle text-danger mr-2"></i> Suspendido
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-center d-none d-md-table-cell">
                                <?php if($socio['tiene_huella']): ?>
                                    <span class="badge badge-success" title="Huella registrada">
                                        <i class="fas fa-fingerprint"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" title="Sin huella">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="pr-4 align-middle text-right">
                                <div class="dropdown no-arrow d-inline-block">
                                    <a class="btn btn-sm btn-white border shadow-sm dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-cog fa-sm text-gray-600"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                        <a class="dropdown-item text-primary" href="index.php?action=socios_edit&accion=<?php echo $socio['accion']; ?>">
                                            <i class="fas fa-edit fa-sm fa-fw mr-2"></i> Editar Socio
                                        </a>
                                        
                                        <div class="dropdown-divider"></div>
                                        
                                        <?php if($socio['tiene_huella']): ?>
                                            <a class="dropdown-item text-success" href="#" onclick="abrirModalHuella('<?php echo htmlspecialchars($socio['cedula']); ?>', '<?php echo htmlspecialchars($socio['nombre']); ?>')">
                                                <i class="fas fa-fingerprint fa-sm fa-fw mr-2"></i> Actualizar Huella
                                            </a>
                                            <a class="dropdown-item text-warning" href="#" onclick="eliminarHuella('<?php echo htmlspecialchars($socio['cedula']); ?>', '<?php echo htmlspecialchars($socio['nombre']); ?>')">
                                                <i class="fas fa-times-circle fa-sm fa-fw mr-2"></i> Eliminar Huella
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item text-info" href="#" onclick="abrirModalHuella('<?php echo htmlspecialchars($socio['cedula']); ?>', '<?php echo htmlspecialchars($socio['nombre']); ?>')">
                                                <i class="fas fa-fingerprint fa-sm fa-fw mr-2"></i> Registrar Huella
                                            </a>
                                        <?php endif; ?>

                                        <div class="dropdown-divider"></div>
                                        
                                        <form action="index.php?action=socios_delete" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Está seguro de eliminar este socio?');">
                                            <?php echo Csrf::tokenField(); ?>
                                            <input type="hidden" name="accion" value="<?php echo htmlspecialchars($socio['accion']); ?>">
                                            <button type="submit" class="dropdown-item text-danger bg-transparent border-0 w-100 text-left">
                                                <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i> Eliminar Socio
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3 border-top-0">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <small class="text-muted">Total de registros: <strong><?php echo $total_registros; ?></strong></small>
                </div>
                <div class="col-sm-6 text-right">
                    <?php if ($total_paginas > 1): ?>
                        <?php 
                            $query_params = "";
                            if (!empty($search['nombre'])) $query_params .= "&search_nombre=" . urlencode($search['nombre']);
                            if (!empty($search['cedula'])) $query_params .= "&search_cedula=" . urlencode($search['cedula']);
                            if (!empty($search['accion'])) $query_params .= "&search_accion=" . urlencode($search['accion']);
                        ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=socios&page=1<?php echo $query_params; ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=socios&page=<?php echo $pagina - 1; ?><?php echo $query_params; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php 
                                $start = max(1, $pagina - 2);
                                $end = min($total_paginas, $pagina + 2);
                                for ($i = $start; $i <= $end; $i++): 
                                ?>
                                    <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?action=socios&page=<?php echo $i; ?><?php echo $query_params; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=socios&page=<?php echo $pagina + 1; ?><?php echo $query_params; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?action=socios&page=<?php echo $total_paginas; ?><?php echo $query_params; ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Captura Huella -->
<div class="modal fade" id="modalHuella" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Huella - <span id="nombreSocioHuella"></span></h5>
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
        document.getElementById('nombreSocioHuella').textContent = nombre;
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
            // 1. Capturar Huella 1 (Índice Derecho)
            estado.textContent = '⏳ Capturando Huella 1 (Índice Derecho). Coloque su dedo...';
            console.log('[Huella] Conectando al bridge en http://localhost:8888/capture para Huella 1...');
            
            const driver = '<?php echo $huellaConfig["driver"]; ?>';
            const ip = '<?php echo $huellaConfig["hik_ip"]; ?>';
            const port = '<?php echo $huellaConfig["hik_port"]; ?>';
            const user = '<?php echo $huellaConfig["hik_user"]; ?>';
            const pass = '<?php echo $huellaConfig["hik_pass"]; ?>';

            const url = `http://localhost:8888/capture?driver=${driver}&ip=${ip}&port=${port}&user=${user}&pass=${pass}`;
            
            const response1 = await fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: { 'Accept': 'application/json' }
            });
            
            if (!response1.ok) throw new Error(`Error HTTP ${response1.status}: ${response1.statusText}`);
            const data1 = await response1.json();
            
            if (data1.status !== 'success') {
                throw new Error(data1.message || 'Error al capturar la primera huella');
            }
            
            // 2. Capturar Huella 2 (Índice Izquierdo)
            estado.textContent = '✓ Huella 1 lista. Ahora, coloque su SEGUNDO DEDO (Índice Izquierdo) en el sensor...';
            estado.className = 'alert alert-warning';
            icono.style.color = '#17a2b8';
            
            // Pequeña pausa para que el usuario cambie de dedo
            await new Promise(r => setTimeout(r, 2000));
            
            console.log('[Huella] Conectando al bridge para Huella 2...');
            const response2 = await fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: { 'Accept': 'application/json' }
            });
            
            if (!response2.ok) throw new Error(`Error HTTP ${response2.status}: ${response2.statusText}`);
            const data2 = await response2.json();
            
            if (data2.status !== 'success') {
                throw new Error(data2.message || 'Error al capturar la segunda huella');
            }
            
            // 3. Validar que no sea el mismo dedo exacto usando el Bridge (Biometría Real)
            estado.textContent = 'Verificando huellas (Deduplicación)...';
            
            try {
                const sameFingerRes = await fetch(`http://localhost:8888/verify`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ template1: data1.template, template2: data2.template })
                }).then(r => r.json());
                
                if (sameFingerRes.match) {
                    throw new Error('Ha capturado el MISMO DEDO dos veces. Por favor use dedos diferentes para la Huella 1 y Huella 2.');
                }
            } catch (e) {
                if(e.message.includes('MISMO DEDO')) throw e;
                console.warn("No se pudo verificar el mismo dedo localmente, continuando...", e);
            }
            
            // 4. Deduplicación 1:N contra toda la base de datos
            estado.textContent = 'Buscando duplicados en la base de datos (Fraude 1:N)... esto puede tardar unos segundos.';
            const dbRes = await fetch('index.php?action=huella_get_all');
            const dbData = await dbRes.json();
            
            if (dbData.success && dbData.templates) {
                let foundMatch = null;
                const bridgeVerifyUrl = `http://localhost:8888/verify`;
                
                for (const t of dbData.templates) {
                    if (t.cedula === cedulaActual) continue; // Saltar al mismo socio
                    
                    // Comparar huella 1
                    let mr = await fetch(bridgeVerifyUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ template1: data1.template, template2: t.template })
                    }).then(r => r.json()).catch(() => ({match: false}));
                    
                    if (mr.match) { foundMatch = t.cedula; break; }
                    
                    // Comparar huella 2
                    mr = await fetch(bridgeVerifyUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ template1: data2.template, template2: t.template })
                    }).then(r => r.json()).catch(() => ({match: false}));
                    
                    if (mr.match) { foundMatch = t.cedula; break; }
                }
                
                if (foundMatch) {
                    throw new Error(`¡FRAUDE DETECTADO! Una de las huellas capturadas ya pertenece al socio con Cédula ${foundMatch}.`);
                }
            }
            
            // 5. Guardar en PHP con type='socio'
            estado.textContent = `✓ Huellas validadas biométricamente. Guardando en base de datos segura...`;
            estado.className = 'alert alert-info';
            icono.style.color = '#28a745';
            
            console.log('[Huella] Guardando en base de datos...');
            const saveResponse = await fetch('index.php?action=huella_save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cedula=${encodeURIComponent(cedulaActual)}&template=${encodeURIComponent(data1.template)}&template2=${encodeURIComponent(data2.template)}&type=socio`
            });
            
            const saveData = await saveResponse.json();
            console.log('[Huella] Respuesta del guardado:', saveData);
            
            if (saveData.success) {
                estado.textContent = '✓ ¡Doble huella guardada exitosamente en el sistema!';
                estado.className = 'alert alert-success';
                icono.style.color = '#28a745';
                
                setTimeout(() => {
                    $('#modalHuella').modal('hide');
                    location.reload(); // Recargar para actualizar el estado
                }, 2000);
            } else {
                throw new Error(saveData.message || 'Error al guardar en base de datos');
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
                body: `cedula=${encodeURIComponent(cedula)}&type=socio&csrf_token=${encodeURIComponent(token)}`
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
