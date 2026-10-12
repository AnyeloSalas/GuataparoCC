<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<style>
    .ai-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .ai-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
    }
    .chat-box {
        height: 400px;
        overflow-y: auto;
        background-color: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
        border: 1px solid #e3e6f0;
    }
    .message {
        margin-bottom: 15px;
        max-width: 80%;
        padding: 12px 18px;
        border-radius: 15px;
        position: relative;
        animation: fadeIn 0.3s ease;
    }
    .message-user {
        background: #4e73df;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 2px;
    }
    .message-ai {
        background: white;
        color: #5a5c69;
        border: 1px solid #e3e6f0;
        margin-right: auto;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .typing-indicator span {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #ccc;
        border-radius: 50%;
        animation: typing 1s infinite;
        margin: 0 2px;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typing {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .ai-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 10px;
    }
</style>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-robot text-primary mr-2"></i>Guataparo AI
        </h1>
        <div>
            <?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
            <a href="index.php?action=ia_entrenamiento" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm mr-2">
                <i class="fas fa-brain fa-sm text-dark-50 mr-1"></i> Entrenar IA
            </a>
            <?php endif; ?>
            <span class="d-none d-sm-inline-block badge badge-success shadow-sm">
                <i class="fas fa-check-circle fa-sm text-white-50 mr-1"></i> Sistema Operativo
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Bienvenida / Resumen -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 ai-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-primary text-white">
                    <h6 class="m-0 font-weight-bold">Análisis Inteligente</h6>
                    <i class="fas fa-brain fa-lg"></i>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 15rem;" src="assets/img/undraw_artificial_intelligence.svg" alt="AI Analysis">
                        <!-- Si no existe la imagen, usar un icono grande -->
                        <div class="d-none"><i class="fas fa-microchip fa-5x text-gray-300"></i></div>
                    </div>
                    <p>Hola, soy tu asistente inteligente. He analizado los datos del club y aquí tienes un resumen rápido:</p>
                    
                    <div class="mb-3">
                        <h4 class="small font-weight-bold">Salud de la Base de Datos <span class="float-right">98%</span></h4>
                        <div class="progress mb-4">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 98%" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-left-info" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Insight:</strong> Se detecta una actividad normal en el registro de socios. No hay anomalías reportadas hoy.
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4 ai-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Asistente Virtual</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Opciones:</div>
                            <a class="dropdown-item" href="#" onclick="limpiarChat()">Limpiar conversación</a>
                            <a class="dropdown-item" href="#">Exportar análisis</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chatBox" class="chat-box mb-3">
                        <!-- Mensaje inicial -->
                        <div class="d-flex align-items-start mb-3">
                            <div class="ai-avatar"><i class="fas fa-robot"></i></div>
                            <div class="message message-ai">
                                ¡Hola! Soy Guataparo AI. ¿En qué puedo ayudarte hoy? Puedes preguntarme sobre estadísticas de socios, resultados de votaciones o solicitar un análisis general.
                            </div>
                        </div>
                    </div>
                    
                    <form id="chatForm" onsubmit="enviarPregunta(event)" class="d-flex">
                        <input type="text" id="userQuery" class="form-control bg-light border-0 small mr-2" 
                               placeholder="Escribe tu pregunta aquí... (ej: ¿Cuántos socios hay?)" 
                               aria-label="Search" aria-describedby="basic-addon2" style="height: 50px;">
                        <button class="btn btn-primary" type="submit" style="width: 50px;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function limpiarChat() {
        const chatBox = document.getElementById('chatBox');
        chatBox.innerHTML = `
            <div class="d-flex align-items-start mb-3">
                <div class="ai-avatar"><i class="fas fa-robot"></i></div>
                <div class="message message-ai">
                    Chat limpiado. ¿En qué más puedo ayudarte?
                </div>
            </div>
        `;
    }

    async function enviarPregunta(e) {
        e.preventDefault();
        const input = document.getElementById('userQuery');
        const pregunta = input.value.trim();
        const chatBox = document.getElementById('chatBox');

        if (!pregunta) return;

        // 1. Agregar mensaje del usuario
        const userMsgHTML = `
            <div class="d-flex align-items-end flex-column mb-3">
                <div class="message message-user">
                    ${pregunta}
                </div>
            </div>
        `;
        chatBox.insertAdjacentHTML('beforeend', userMsgHTML);
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // 2. Mostrar indicador de "Escribiendo..."
        const loadingId = 'loading-' + Date.now();
        const loadingHTML = `
            <div id="${loadingId}" class="d-flex align-items-start mb-3">
                <div class="ai-avatar"><i class="fas fa-robot"></i></div>
                <div class="message message-ai">
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        chatBox.insertAdjacentHTML('beforeend', loadingHTML);
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            // 3. Llamada al backend (simulada por ahora o real si conectamos controlador)
            const response = await fetch('index.php?action=ia_query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ pregunta: pregunta })
            });

            const data = await response.json();

            // 4. Remover loading y mostrar respuesta
            document.getElementById(loadingId).remove();
            
            // Formatear respuesta (simple markdown a html básico)
            let respuestaFormateada = data.respuesta
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Negritas
                .replace(/\n/g, '<br>'); // Saltos de línea

            const aiMsgHTML = `
                <div class="d-flex align-items-start mb-3">
                    <div class="ai-avatar"><i class="fas fa-robot"></i></div>
                    <div class="message message-ai">
                        ${respuestaFormateada}
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', aiMsgHTML);
            
        } catch (error) {
            document.getElementById(loadingId).remove();
            const errorHTML = `
                <div class="d-flex align-items-start mb-3">
                    <div class="ai-avatar bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="message message-ai text-danger">
                        Lo siento, hubo un error al procesar tu solicitud. Intenta nuevamente.
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', errorHTML);
        }
        
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
