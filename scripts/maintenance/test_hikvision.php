<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Hikvision - Captura de Huella</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .log-box { background: #2d2d2d; color: #00ff00; font-family: 'Consolas', monospace; padding: 15px; border-radius: 5px; height: 300px; overflow-y: auto; font-size: 0.9rem; }
        .status-badge { font-size: 1.2rem; }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="text-primary"><i class="fas fa-fingerprint"></i> Prueba de Integración Hikvision (SDK)</h2>
            <p class="text-muted">Herramienta de diagnóstico para el dispositivo DS-K1F820-F</p>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Configuración -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-cogs"></i> Configuración de Conexión
                </div>
                <div class="card-body">
                    <form id="configForm">
                        <div class="mb-3">
                            <label class="form-label">Dirección IP</label>
                            <input type="text" class="form-control" id="ip" value="192.0.0.64" placeholder="Ej: 192.168.1.64">
                            <small class="text-muted">Para USB local use 127.0.0.1 o la IP asignada.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" class="form-control" id="port" value="8000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="user" value="admin">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="pass" value="12345">
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="iniciarCaptura('capture')">
                                <i class="fas fa-fingerprint"></i> Probar Captura
                            </button>
                            
                            <hr>
                            <h6 class="text-muted text-center">Opciones Avanzadas</h6>
                            
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="cardNo" placeholder="N° Tarjeta">
                                <button class="btn btn-outline-primary" type="button" onclick="iniciarCaptura('get_fingerprint')">
                                    <i class="fas fa-download"></i> Descargar Huella
                                </button>
                            </div>

                            <button type="button" class="btn btn-warning" onclick="iniciarCaptura('mock')">
                                <i class="fas fa-robot"></i> Probar Simulación (Mock)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Asegúrese de que el archivo <code>bridge_hik.exe</code> esté compilado y que <code>router.php</code> esté ejecutándose en el puerto 8888.
            </div>
        </div>

        <!-- Panel de Resultados -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-terminal"></i> Log de Ejecución en Tiempo Real</span>
                    <button class="btn btn-sm btn-outline-light" onclick="limpiarLog()">Limpiar</button>
                </div>
                <div class="card-body bg-light">
                    <div id="statusResult" class="text-center mb-3">
                        <span class="badge bg-secondary status-badge">Esperando acción...</span>
                    </div>
                    
                    <div id="logBox" class="log-box"></div>
                    
                    <div class="mt-3">
                        <label class="form-label">Resultado (Template Base64):</label>
                        <textarea class="form-control" id="resultTemplate" rows="4" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function log(msg, type = 'info') {
        const box = document.getElementById('logBox');
        const time = new Date().toLocaleTimeString();
        let color = '#00ff00';
        if(type === 'error') color = '#ff5555';
        if(type === 'warning') color = '#ffff55';
        
        box.innerHTML += `<div style="color:${color}">[${time}] ${msg}</div>`;
        box.scrollTop = box.scrollHeight;
    }

    function limpiarLog() {
        document.getElementById('logBox').innerHTML = '';
        document.getElementById('resultTemplate').value = '';
        document.getElementById('statusResult').innerHTML = '<span class="badge bg-secondary status-badge">Esperando acción...</span>';
    }

    async function iniciarCaptura(mode) {
        const ip = mode === 'mock' ? 'mock' : document.getElementById('ip').value;
        const port = document.getElementById('port').value;
        const user = document.getElementById('user').value;
        const pass = document.getElementById('pass').value;
        const cardNo = document.getElementById('cardNo').value;
        
        limpiarLog();
        document.getElementById('statusResult').innerHTML = '<span class="badge bg-warning text-dark status-badge"><i class="fas fa-spinner fa-spin"></i> Ejecutando...</span>';
        
        let url = `http://localhost:8888/capture?driver=hikvision&ip=${ip}&port=${port}&user=${user}&pass=${pass}`;
        
        if (mode === 'get_fingerprint') {
            if(!cardNo) {
                alert('Debe ingresar un número de tarjeta');
                return;
            }
            url = `http://localhost:8888/get_fingerprint?ip=${ip}&port=${port}&user=${user}&pass=${pass}&card=${cardNo}`;
            log(`Iniciando descarga para tarjeta: ${cardNo}...`, 'info');
        } else {
            log(`Iniciando captura en ${ip}:${port}...`, 'info');
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000); // 15s timeout
            
            const response = await fetch(url, { 
                method: 'GET',
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const text = await response.text();
            log(`Respuesta cruda recibida: ${text.substring(0, 100)}...`, 'warning');
            
            let data;
            try {
                // Try clean parse if mixed output
                const firstBrace = text.indexOf('{');
                const lastBrace = text.lastIndexOf('}');
                if(firstBrace !== -1 && lastBrace !== -1) {
                    data = JSON.parse(text.substring(firstBrace, lastBrace + 1));
                } else {
                    data = JSON.parse(text);
                }
            } catch(e) {
                log('Error parseando JSON: ' + e.message, 'error');
                console.error(text);
                throw new Error('Respuesta inválida del servidor');
            }

            if (data.status === 'success') {
                log('¡Operación Exitosa!', 'info');
                document.getElementById('statusResult').innerHTML = '<span class="badge bg-success status-badge"><i class="fas fa-check"></i> ÉXITO</span>';
                document.getElementById('resultTemplate').value = data.template;
                
                if(data.quality) log(`Calidad de Huella: ${data.quality}/10`, 'info');
                if(data.finger_id) log(`ID Dedo: ${data.finger_id}`, 'info');
            } else {
                log(`Error reportado por API: ${data.message}`, 'error');
                document.getElementById('statusResult').innerHTML = '<span class="badge bg-danger status-badge"><i class="fas fa-times"></i> ERROR</span>';
            }

        } catch (error) {
            log(`Excepción: ${error.message}`, 'error');
            document.getElementById('statusResult').innerHTML = '<span class="badge bg-danger status-badge"><i class="fas fa-bomb"></i> FALLO</span>';
            
            if(error.message.includes('Fetch')) {
               log('Sugerencia: Verifique que router.php esté corriendo (php -S localhost:8888)', 'warning');
            }
        }
    }
</script>

</body>
</html>
