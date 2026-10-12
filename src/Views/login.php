<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login - Sistema Guataparo">
    <meta name="author" content="">

    <title>Login - Sistema Guataparo</title>

    <!-- Custom fonts for this template-->
    <link href="assets/vendor/css/fontawesome.min.css" rel="stylesheet" type="text/css">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">

    <!-- Custom styles for this template-->
    <link href="assets/vendor/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #001a72;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #001a72;
            --text-muted: #6c757d;
            --input-border: #e2e8f0;
        }

        body {
            background-color: var(--bg-color) !important;
            background-image: url('assets/img/entrada.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 24px;
        }
        .logo-container img {
            max-width: 180px;
            height: auto;
        }

        .welcome-title {
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 4px;
        }
        .welcome-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 32px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            font-size: 1.1rem;
        }

        .form-control-custom {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 26, 114, 0.1);
        }

        .btn-login {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #00135a;
        }

        .btn-fingerprint {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-fingerprint:hover {
            background-color: rgba(0, 26, 114, 0.05);
        }

        .footer-text {
            margin-top: 32px;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .forgot-password {
            display: block;
            margin-top: 16px;
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Alert styling */
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-card {
                padding: 24px;
            }
            .welcome-title {
                font-size: 1.5rem;
            }
            .logo-container img {
                max-width: 140px;
            }
        }
    </style>

</head>

<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="assets/img/logo_guataparo2.png" alt="Guataparo Country Club">
        </div>
        
        <h1 class="welcome-title">Bienvenido</h1>
        <p class="welcome-subtitle">SGV - Sistema de Gestión de Votaciones</p>

        <?php 
        // Mostrar mensajes flash
        if ($successMsg = flash('success')): 
        ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle mr-1"></i> <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>

        <?php 
        if ($errorMsg = flash('error')): 
        ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <?php 
        if(isset($error)): 
        ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=login" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Ingresando...';">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
            
            <div class="form-group">
                <label class="form-label" for="usuario">Usuario</label>
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="usuario" id="usuario" class="form-control-custom" 
                           placeholder="Ingrese su usuario" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control-custom" 
                           placeholder="Ingrese su contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Iniciar Sesión
            </button>
            
            <button type="button" class="btn-fingerprint" onclick="abrirModalHuella()">
                <i class="bi bi-fingerprint"></i> Ingresar con Huella
            </button>
        </form>
        
        <a class="forgot-password" href="index.php?action=forgot_password">¿Olvidaste tu contraseña?</a>

        <div class="footer-text">
            © <?php echo date('Y'); ?> Guataparo Country Club
        </div>
    </div>

    <!-- Modal Captura Huella -->
    <div class="modal fade" id="modalHuella" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ingreso Biométrico</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div id="estadoHuella" class="alert alert-info">
                        Presione "Iniciar" y coloque su dedo en el sensor.
                    </div>
                    <div class="my-3">
                        <i class="fas fa-fingerprint" style="font-size: 4rem; color: #6c757d;" id="iconoHuella"></i>
                    </div>
                    <div class="progress mb-3 d-none" id="progresoHuella">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="button" onclick="iniciarCaptura()">Iniciar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/js/jquery.min.js"></script>
    <script src="assets/vendor/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/js/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="assets/vendor/js/sb-admin-2.min.js"></script>

    <script>
        function abrirModalHuella() {
            document.getElementById('estadoHuella').className = 'alert alert-info';
            document.getElementById('estadoHuella').textContent = 'Presione "Iniciar" y coloque su dedo en el sensor.';
            document.getElementById('iconoHuella').style.color = '#6c757d';
            $('#modalHuella').modal('show');
        }

        // URL del bridge local (configurable desde .env via PHP)
        const BRIDGE_URL = '<?php echo rtrim($huellaConfig["bridge_url"] ?? "http://localhost:8888", "/"); ?>';

        async function iniciarCaptura() {
            const estado   = document.getElementById('estadoHuella');
            const icono    = document.getElementById('iconoHuella');
            const progreso = document.getElementById('progresoHuella');
            const btnIniciar = event.target;

            btnIniciar.disabled = true;
            progreso.classList.remove('d-none');

            try {
                // ── PASO 1: Capturar huella en la PC local (lector físico) ──────────────
                estado.textContent = '⏳ Paso 1/4 — Coloque el dedo en el sensor...';
                estado.className = 'alert alert-warning';
                icono.style.color = '#ffc107';

                console.log('[Huella] Conectando al bridge local:', BRIDGE_URL);
                const captureRes = await fetch(`${BRIDGE_URL}/capture?mode=login`, {
                    method: 'GET',
                    mode: 'cors',
                    headers: { 'Accept': 'application/json' }
                });

                if (!captureRes.ok) throw new Error(`Bridge HTTP ${captureRes.status}`);
                const captureData = await captureRes.json();
                console.log('[Huella] Captura resultado:', captureData.status);

                if (captureData.status !== 'success' || !captureData.template) {
                    throw new Error(captureData.message || 'No se pudo capturar la huella');
                }
                const liveTemplate = captureData.template;

                // ── PASO 2: Obtener templates almacenados del servidor ────────────────────
                estado.textContent = '⏳ Paso 2/4 — Obteniendo templates del servidor...';
                icono.style.color = '#17a2b8';

                const tplRes = await fetch('index.php?action=get_fingerprint_templates', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
                const tplData = await tplRes.json();
                console.log('[Huella] Templates recibidos:', tplData.total);

                if (tplData.status !== 'ok' || !tplData.templates || tplData.templates.length === 0) {
                    throw new Error('No hay huellas registradas en el sistema.');
                }

                // ── PASO 3: Comparar localmente vía bridge (1 llamada a batch_match) ───────────
                estado.textContent = `⏳ Paso 3/4 — Comparando con ${tplData.total} huellas registradas...`;
                icono.style.color = '#6f42c1';

                const templatesList = tplData.templates.map(t => t.template);
                
                const matchRes = await fetch(`${BRIDGE_URL}/batch_match`, {
                    method: 'POST',
                    mode: 'cors',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        template: liveTemplate,
                        templates: templatesList
                    })
                });
                const matchData = await matchRes.json();
                console.log(`[Huella] Resultado batch_match:`, matchData);

                let bestIdx = null;
                let bestScore = 0;
                
                if (matchData.status === 'success' && matchData.match) {
                    bestScore = matchData.score;
                    // matchData.idx es el índice en el array enviado
                    const originalIdx = tplData.templates[matchData.idx].idx;
                    bestIdx = originalIdx;
                }

                console.log(`[Huella] Mejor coincidencia: original_idx=${bestIdx}, score=${bestScore}`);

                if (bestIdx === null || bestScore < 50) {
                    throw new Error(`Huella no reconocida (score: ${bestScore}, mínimo: 50)`);
                }

                // ── PASO 4: Enviar resultado al servidor para crear sesión ────────────────
                estado.textContent = '⏳ Paso 4/4 — Verificando identidad en el servidor...';
                icono.style.color = '#0dcaf0';

                const loginRes = await fetch('index.php?action=login_huella', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        template:     liveTemplate,
                        matched_idx:  bestIdx,
                        score:        bestScore
                    })
                });
                const loginData = await loginRes.json();
                console.log('[Huella] Respuesta del servidor:', loginData);

                if (loginData.success) {
                    estado.innerHTML = `<strong>✓ ¡Bienvenido ${loginData.usuario || ''}!</strong><br>Redirigiendo al sistema...`;
                    estado.className = 'alert alert-success';
                    icono.style.color = '#28a745';
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
                } else {
                    throw new Error(loginData.message || 'Huella no reconocida en el servidor');
                }

            } catch (error) {
                console.error('[Huella] Error:', error);

                let msg = error.message;
                if (msg.includes('NetworkError') || msg.includes('Failed to fetch') || msg.includes('ERR_CONNECTION_REFUSED')) {
                    msg = `No se pudo conectar al bridge local (${BRIDGE_URL}).<br>
                           Asegúrese de que <strong>bridge_router.php esté ejecutándose</strong> en la PC con el lector:<br>
                           <code>php -S 0.0.0.0:8888 huella\\bridge_router.php</code>`;
                }

                estado.innerHTML = '<strong>✗ Error:</strong><br>' + msg;
                estado.className = 'alert alert-danger';
                icono.style.color = '#dc3545';
            } finally {
                progreso.classList.add('d-none');
                btnIniciar.disabled = false;
            }
        }
    </script>

    <!-- Botón Flotante IA -->
    <div id="ai-floating-btn" onclick="toggleChat()">
        <i class="fas fa-robot"></i>
    </div>

    <!-- Ventana de Chat Flotante -->
    <div id="ai-chat-window">
        <div class="chat-header">
            <span><i class="fas fa-robot mr-2"></i>Asistente Guataparo</span>
            <button type="button" class="close text-white" onclick="toggleChat()" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="chat-body" id="loginChatBox">
            <div class="d-flex align-items-start mb-3">
                <div class="ai-avatar-small"><i class="fas fa-robot"></i></div>
                <div class="message-bubble message-ai-small">
                    ¡Hola! Soy tu asistente virtual. ¿Tienes dudas sobre el acceso o el club?
                </div>
            </div>
        </div>
        <div class="chat-footer">
            <form onsubmit="enviarPreguntaLogin(event)" class="d-flex">
                <input type="text" id="loginUserQuery" class="form-control form-control-sm mr-2" placeholder="Escribe tu duda..." required>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <style>
        #ai-floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            animation: float 3s ease-in-out infinite;
        }
        #ai-floating-btn:hover {
            transform: scale(1.1);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        #ai-chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #e3e6f0;
        }
        
        .chat-header {
            background: #4e73df;
            color: white;
            padding: 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fc;
        }
        
        .chat-footer {
            padding: 10px;
            background: white;
            border-top: 1px solid #e3e6f0;
        }
        
        .ai-avatar-small {
            width: 30px;
            height: 30px;
            background: #4e73df;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            margin-right: 8px;
            flex-shrink: 0;
        }
        
        .message-bubble {
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            max-width: 85%;
        }
        
        .message-ai-small {
            background: white;
            border: 1px solid #e3e6f0;
            color: #5a5c69;
            border-bottom-left-radius: 2px;
        }
        
        .message-user-small {
            background: #4e73df;
            color: white;
            border-bottom-right-radius: 2px;
            margin-left: auto;
        }

        /* Animación de entrada */
        .slide-in-bottom {
            animation: slide-in-bottom 0.4s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
        }
        @keyframes slide-in-bottom {
            0% { transform: translateY(50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('ai-chat-window');
            if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
                chatWindow.style.display = 'flex';
                chatWindow.classList.add('slide-in-bottom');
                document.getElementById('loginUserQuery').focus();
            } else {
                chatWindow.style.display = 'none';
            }
        }

        async function enviarPreguntaLogin(e) {
            e.preventDefault();
            const input = document.getElementById('loginUserQuery');
            const pregunta = input.value.trim();
            const chatBox = document.getElementById('loginChatBox');

            if (!pregunta) return;

            // 1. Mostrar mensaje usuario
            chatBox.insertAdjacentHTML('beforeend', `
                <div class="d-flex align-items-end flex-column mb-3">
                    <div class="message-bubble message-user-small">${pregunta}</div>
                </div>
            `);
            input.value = '';
            chatBox.scrollTop = chatBox.scrollHeight;

            // 2. Loading
            const loadingId = 'loading-' + Date.now();
            chatBox.insertAdjacentHTML('beforeend', `
                <div id="${loadingId}" class="d-flex align-items-start mb-3">
                    <div class="ai-avatar-small"><i class="fas fa-robot"></i></div>
                    <div class="message-bubble message-ai-small">
                        <i class="fas fa-circle-notch fa-spin"></i> Escribiendo...
                    </div>
                </div>
            `);
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                // 3. Consulta al backend
                const response = await fetch('index.php?action=ia_query', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pregunta: pregunta })
                });
                const data = await response.json();

                // 4. Mostrar respuesta
                document.getElementById(loadingId).remove();
                
                let respuestaFmt = data.respuesta
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n/g, '<br>');

                chatBox.insertAdjacentHTML('beforeend', `
                    <div class="d-flex align-items-start mb-3">
                        <div class="ai-avatar-small"><i class="fas fa-robot"></i></div>
                        <div class="message-bubble message-ai-small">${respuestaFmt}</div>
                    </div>
                `);
            } catch (error) {
                document.getElementById(loadingId).remove();
                chatBox.insertAdjacentHTML('beforeend', `
                    <div class="d-flex align-items-start mb-3">
                        <div class="ai-avatar-small bg-danger"><i class="fas fa-exclamation"></i></div>
                        <div class="message-bubble message-ai-small text-danger">Error de conexión.</div>
                    </div>
                `);
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>

</body>

</html>
