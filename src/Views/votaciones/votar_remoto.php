<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Votación Remota - Guataparo Country Club</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.4);
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.4);
            --bg-gradient-start: #f8fafc;
            --bg-gradient-end: #e2e8f0;
            --card-bg: rgba(255, 255, 255, 0.85);
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease-out;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 1.1rem;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.5);
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .header strong {
            color: var(--primary);
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .candidate-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .candidate-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .candidate-card.selected-aceptado {
            border: 2px solid var(--success);
            background: linear-gradient(to bottom, var(--card-bg), rgba(16, 185, 129, 0.05));
        }

        .candidate-card.selected-rechazado {
            border: 2px solid var(--danger);
            background: linear-gradient(to bottom, var(--card-bg), rgba(239, 68, 68, 0.05));
        }

        .selection-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .candidate-card.selected-aceptado .selection-badge.si {
            opacity: 1;
            transform: scale(1);
            color: var(--success);
        }

        .candidate-card.selected-rechazado .selection-badge.no {
            opacity: 1;
            transform: scale(1);
            color: var(--danger);
        }

        .avatar-container {
            width: 140px;
            height: 140px;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .candidate-card:hover .avatar {
            transform: scale(1.05);
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #94a3b8;
            border: 4px solid white;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .candidate-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        .candidate-role {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .vote-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-vote {
            flex: 1;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 2px solid transparent;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            color: var(--text-main);
        }
        
        .btn-vote i {
            font-size: 1.1rem;
        }

        .btn-vote-si {
            border-color: rgba(16, 185, 129, 0.3);
        }

        .btn-vote-si:hover {
            color: var(--success);
            background: rgba(16, 185, 129, 0.05);
            border-color: var(--success);
        }

        .btn-vote-no {
            border-color: rgba(239, 68, 68, 0.3);
        }

        .btn-vote-no:hover {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
            border-color: var(--danger);
        }

        /* Active States */
        .btn-vote-si.active {
            background: var(--success);
            color: white;
            border-color: var(--success);
            box-shadow: 0 4px 15px var(--success-glow);
            transform: translateY(-2px);
        }

        .btn-vote-no.active {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            box-shadow: 0 4px 15px var(--danger-glow);
            transform: translateY(-2px);
        }

        .submit-container {
            text-align: center;
            margin-top: 2rem;
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.2s;
            opacity: 0;
            position: sticky;
            bottom: 2rem;
            z-index: 100;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
        }

        .btn-submit:disabled {
            background: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .candidates-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 2rem;
            }
        }

        /* Notification Styling */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: #fff;
            padding: 1.2rem 2rem;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1000;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid var(--danger);
            font-weight: 500;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .notification-icon {
            color: var(--danger);
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Notification Toast -->
    <div id="notification" class="notification">
        <i class="fas fa-exclamation-triangle notification-icon"></i>
        <span id="notificationMessage">Debe votar por todos los candidatos (Sí/No).</span>
    </div>

    <div class="container">
        <div class="header">
            <h1>Votación en Línea</h1>
            <p>Está votando en representación de la acción <strong><?php echo htmlspecialchars($accion ?? ''); ?></strong></p>
        </div>

        <form action="index.php?action=votaciones_remoto&token=<?php echo htmlspecialchars($token ?? ''); ?>" method="POST" id="formVoto">
            <?php echo Csrf::tokenField(); ?>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
            <div class="candidates-grid">
                <?php if(!empty($postulados)): ?>
                    <?php 
                    $delay = 0.1;
                    foreach($postulados as $index => $p): 
                    ?>
                        <div class="candidate-card" id="card_<?php echo $p['cedula']; ?>" style="animation-delay: <?php echo $delay; ?>s;">
                            <div class="selection-badge si"><i class="fas fa-check-circle"></i></div>
                            <div class="selection-badge no"><i class="fas fa-times-circle"></i></div>

                            <div class="avatar-container">
                                <?php if(!empty($p['foto'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" alt="Foto" class="avatar">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h2 class="candidate-name"><?php echo htmlspecialchars($p['primer_nombre'] . ' ' . $p['primer_apellido']); ?></h2>
                            <p class="candidate-role"><?php echo htmlspecialchars($p['profesion']); ?></p>

                            <input type="hidden" name="votos[<?php echo $p['cedula']; ?>]" id="input_voto_<?php echo $p['cedula']; ?>" value="">

                            <div class="vote-actions">
                                <button type="button" class="btn-vote btn-vote-si" onclick="seleccionarVoto('<?php echo $p['cedula']; ?>', 'ACEPTADO', this)">
                                    <i class="fas fa-thumbs-up"></i> SÍ
                                </button>
                                <button type="button" class="btn-vote btn-vote-no" onclick="seleccionarVoto('<?php echo $p['cedula']; ?>', 'RECHAZADO', this)">
                                    <i class="fas fa-thumbs-down"></i> NO
                                </button>
                            </div>
                        </div>
                    <?php 
                    $delay += 0.1;
                    endforeach; 
                    ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 4rem; background: var(--card-bg); border-radius: 20px; width: 100%; grid-column: 1 / -1; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <i class="fas fa-inbox mb-3" style="font-size: 4rem; color: var(--text-muted);"></i>
                        <h3 style="color: var(--text-main); margin-top: 1rem; font-size: 1.5rem;">No hay candidatos postulados actualmente.</h3>
                    </div>
                <?php endif; ?>
            </div>

            <?php if(!empty($postulados)): ?>
            <div class="submit-container">
                <button type="submit" class="btn-submit" id="btnProcesar">
                    <i class="fas fa-paper-plane"></i> Emitir Votos
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function seleccionarVoto(cedula, voto, btnClicked) {
            const input = document.getElementById('input_voto_' + cedula);
            
            // Allow deselecting
            if (input.value === voto) {
                input.value = '';
                resetCardUI(cedula);
                return;
            }
            
            input.value = voto;
            
            // Update Card
            const card = document.getElementById('card_' + cedula);
            card.classList.remove('selected-aceptado', 'selected-rechazado');
            if (voto === 'ACEPTADO') card.classList.add('selected-aceptado');
            else if (voto === 'RECHAZADO') card.classList.add('selected-rechazado');
            
            // Update Buttons
            const container = btnClicked.parentElement;
            container.querySelector('.btn-vote-si').classList.remove('active');
            container.querySelector('.btn-vote-no').classList.remove('active');
            
            if (voto === 'ACEPTADO') {
                container.querySelector('.btn-vote-si').classList.add('active');
            } else {
                container.querySelector('.btn-vote-no').classList.add('active');
            }
        }

        function resetCardUI(cedula) {
            const card = document.getElementById('card_' + cedula);
            card.classList.remove('selected-aceptado', 'selected-rechazado');
            card.querySelectorAll('.btn-vote').forEach(b => b.classList.remove('active'));
        }

        function showNotification(msg) {
            const notif = document.getElementById('notification');
            document.getElementById('notificationMessage').innerText = msg;
            notif.classList.add('show');
            setTimeout(() => notif.classList.remove('show'), 4000);
        }

        document.getElementById('formVoto').addEventListener('submit', function(e) {
            e.preventDefault();

            let faltantes = 0;
            const inps = document.querySelectorAll('input[type="hidden"]');
            inps.forEach(i => { if (i.value === '') faltantes++; });
            
            if (faltantes > 0) {
                // Focus on the first empty item
                for(let i=0; i<inps.length; i++) {
                    if(inps[i].value === '') {
                        const cardId = inps[i].id.replace('input_voto_', 'card_');
                        const card = document.getElementById(cardId);
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.style.animation = 'none';
                        setTimeout(() => {
                            card.style.animation = 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both';
                        }, 50);
                        break;
                    }
                }
                showNotification(`Falta por votar en ${faltantes} candidato(s)`);
                return;
            }
            
            if(confirm('¿Está totalmente seguro de sus elecciones? El voto es definitivo y cerrará la sesión.')) {
                const btn = document.getElementById('btnProcesar');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitiendo Votos...';
                btn.disabled = true;
                this.submit();
            }
        });

        // Add shake keyframes for missing votes
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes shake {
                10%, 90% { transform: translate3d(-1px, 0, 0); }
                20%, 80% { transform: translate3d(2px, 0, 0); border-color: var(--danger); box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }
                30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                40%, 60% { transform: translate3d(4px, 0, 0); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
