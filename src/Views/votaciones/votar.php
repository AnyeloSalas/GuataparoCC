<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$es_socio = (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO');
$totalCandidatos = !empty($postulados) ? count($postulados) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Voto - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="assets/vendor/css/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #172554;
            --card-border: #e2e8f0;
            --bg-body: #f8fafc;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Generic elevated card */
        .vote-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.2s ease-in-out;
        }

        .vote-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            background-color: #ffffff;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .header-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.3rem;
        }

        /* Candidate vote card */
        .candidate-vote-card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 1.25rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .candidate-vote-card:hover {
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        }

        .candidate-vote-card.voted-aceptado {
            border-color: #10b981;
            background: linear-gradient(to right, #f0fdf4 0%, #ffffff 15%);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.12);
        }

        .candidate-vote-card.voted-rechazado {
            border-color: #ef4444;
            background: linear-gradient(to right, #fef2f2 0%, #ffffff 15%);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.12);
        }

        .candidate-avatar {
            width: 68px;
            height: 68px;
            border-radius: 14px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            transition: border-color 0.2s;
        }

        .candidate-avatar-placeholder {
            width: 68px;
            height: 68px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            color: #ffffff;
            background: linear-gradient(135deg, #475569, #334155);
        }

        /* Segmented Decision Buttons */
        .vote-options-group {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .btn-vote-option {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
            background-color: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        .btn-vote-option:hover {
            border-color: #94a3b8;
            color: #334155;
            background-color: #f8fafc;
        }

        /* Estilo activo ACEPTADO */
        .btn-vote-accept.active {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }

        /* Estilo activo RECHAZADO */
        .btn-vote-reject.active {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
            transform: translateY(-1px);
        }

        /* Floating action bar */
        .actions-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            position: sticky;
            bottom: 0;
            z-index: 1020;
            padding: 1rem 0;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
        }

        .btn-confirm-vote {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.2s ease;
            font-size: 1.05rem;
        }

        .btn-confirm-vote:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
            transform: translateY(-1px);
            color: #ffffff;
        }

        /* Biometric pulse animation in modal */
        .fingerprint-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #f1f5f9;
            margin: 0 auto;
        }

        .fingerprint-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #3b82f6;
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.25); opacity: 0.3; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        .nav-link-btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 0.45rem 0.9rem;
        }

        /* Print Media Styles */
        @media print {
            body > *:not(#modalConfirmacion),
            nav,
            .navbar,
            .actions-bar,
            .container,
            .modal-backdrop,
            #modalHuella,
            .d-print-none {
                display: none !important;
            }

            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                visibility: visible !important;
                width: 100% !important;
            }

            #modalConfirmacion {
                position: static !important;
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
                overflow: visible !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }

            #modalConfirmacion .modal-dialog {
                transform: none !important;
                margin: 0 auto !important;
                max-width: 80mm !important;
                width: 100% !important;
            }

            #modalConfirmacion .modal-content {
                border: none !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            #modalConfirmacion .modal-header,
            #modalConfirmacion .modal-footer {
                display: none !important;
            }

            #modalConfirmacion .modal-body {
                padding: 4mm !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
            }

            #printableArea {
                display: block !important;
                visibility: visible !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                font-family: Arial, sans-serif !important;
                font-size: 9pt !important;
                color: #000 !important;
            }

            #printableArea * {
                visibility: visible !important;
                color: #000 !important;
            }

            .d-print-block {
                display: block !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="dashboard.php">
                <i class="bi bi-shield-check fs-4 text-primary-emphasis"></i>
                <span>Sistema Guataparo</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?action=votaciones" class="btn btn-sm btn-outline-light nav-link-btn">
                    <i class="bi bi-box-arrow-left me-1"></i> Volver a Votaciones
                </a>
                <a href="dashboard.php" class="btn btn-sm btn-light nav-link-btn">
                    <i class="bi bi-house-door me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4 pb-5">

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="header-icon bg-success text-white shadow-sm">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-0 text-slate-900">Emisión de Voto Presencial</h2>
                </div>
                <p class="text-muted small mb-0 ps-md-5">
                    Selecciona tu decisión por cada uno de los candidatos en la boleta y confirma con tu huella dactilar.
                </p>
            </div>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Proceso Seguro y Auditado
                </span>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1 fw-semibold"><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="formVotacion" action="index.php?action=votaciones_votar" method="POST">
            <?php echo Csrf::tokenField(); ?>

            <!-- 1. IDENTIFICACIÓN DEL SOCIO VOTANTE -->
            <div class="vote-card mb-4">
                <div class="vote-card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-person-badge fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">1. Identificación del Socio Votante</h5>
                            <small class="text-muted">Asegúrate de que la acción corresponda al titular que ejerce el derecho al sufragio</small>
                        </div>
                    </div>
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                        Paso Obligatorio
                    </span>
                </div>

                <div class="card-body p-4">
                    <?php if(count($acciones) == 1 && $es_socio): ?>
                        <!-- Caso: Socio verificado con una sola acción -->
                        <div class="p-3 bg-success-subtle border border-success-subtle rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-check-fill fs-4"></i>
                                </div>
                                <div>
                                    <span class="badge bg-success text-white rounded-pill mb-1">
                                        <i class="bi bi-fingerprint me-1"></i> Socio Identificado
                                    </span>
                                    <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($acciones[0]['display_text']); ?></h5>
                                    <small class="text-muted">Cédula: <?php echo htmlspecialchars($acciones[0]['cedula']); ?></small>
                                </div>
                            </div>
                            <span class="badge bg-white text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-patch-check-fill me-1"></i> Listo para Votar
                            </span>
                        </div>
                        <input type="hidden" name="accion" id="input_accion" 
                               data-cedula="<?php echo htmlspecialchars($acciones[0]['cedula']); ?>"
                               value="<?php echo htmlspecialchars($acciones[0]['accion']); ?>">
                    <?php else: ?>
                        <!-- Caso: Administrador o Socio con múltiples acciones -->
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-8">
                                <label for="accion" class="form-label fw-bold text-slate-700 small text-uppercase">
                                    Seleccione la Acción o Cédula *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <select class="form-select form-select-lg fw-semibold" id="accion" name="accion" required onchange="actualizarDetalleAccion(this)">
                                        <option value="">Seleccione una acción para iniciar el proceso...</option>
                                        <?php foreach($acciones as $a): ?>
                                            <option value="<?php echo htmlspecialchars($a['accion']); ?>" 
                                                    data-cedula="<?php echo htmlspecialchars($a['cedula']); ?>"
                                                    data-display="<?php echo htmlspecialchars($a['display_text']); ?>">
                                                <?php echo htmlspecialchars($a['display_text']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-text mt-2 text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Busque por número de acción o nombre del socio titular.
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div id="accionPill" class="p-3 bg-light border rounded-3 text-center text-muted small">
                                    <i class="bi bi-person-bounding-box fs-4 d-block mb-1 text-secondary"></i>
                                    <span>Ninguna acción seleccionada</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. BOLETA DE CANDIDATOS -->
            <div class="vote-card mb-4">
                <div class="vote-card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info-emphasis p-2 rounded-3">
                                <i class="bi bi-people-fill fs-5"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0">2. Boleta Electoral de Candidatos</h5>
                                <small class="text-muted">Debes emitir un voto (Aceptado o Rechazado) por cada postulado</small>
                            </div>
                        </div>

                        <!-- Barra de progreso y acciones rápidas -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="votarATodos('ACEPTADO')">
                                <i class="bi bi-check-all me-1"></i> Aceptar a Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="votarATodos('RECHAZADO')">
                                <i class="bi bi-x-circle me-1"></i> Rechazar a Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarVotos()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Mini Progress bar -->
                    <div class="mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-muted text-uppercase">Progreso de la boleta</span>
                            <span id="progresoTexto" class="badge bg-secondary rounded-pill">0 de <?php echo $totalCandidatos; ?> candidatos completados</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="progresoBarra" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if(empty($postulados)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <span class="header-icon bg-warning-subtle text-warning fs-3 p-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold">No hay candidatos habilitados</h5>
                            <p class="text-muted small">No se encuentran postulados activos para el período electoral actual.</p>
                            <a href="index.php?action=votaciones" class="btn btn-secondary btn-sm rounded-pill px-4">
                                Volver al Panel
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach($postulados as $p): 
                                $cedula = htmlspecialchars($p['cedula']);
                                $nombre = htmlspecialchars($p['nombre'] ?? trim(($p['primer_nombre'] ?? '') . ' ' . ($p['primer_apellido'] ?? '')));
                                $profesion = htmlspecialchars($p['profesion'] ?? 'Sin profesión registrada');
                                $correo = htmlspecialchars($p['correo_electronico'] ?? '');
                                $tipoMiembro = strtoupper($p['tipomiembro'] ?? 'PRINCIPAL');
                                $tipoBadge = ($tipoMiembro === 'PRINCIPAL') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle';

                                // Iniciales fallback
                                $palabras = explode(' ', $nombre);
                                $iniciales = '';
                                if (!empty($palabras[0])) $iniciales .= mb_substr($palabras[0], 0, 1);
                                if (!empty($palabras[1])) $iniciales .= mb_substr($palabras[1], 0, 1);
                                $iniciales = strtoupper($iniciales) ?: 'C';
                            ?>
                            <div class="col-12" id="cand_card_container_<?php echo $cedula; ?>">
                                <div class="candidate-vote-card" id="card_<?php echo $cedula; ?>">
                                    <div class="row align-items-center g-3">
                                        
                                        <!-- Avatar y Datos -->
                                        <div class="col-12 col-md-7 d-flex align-items-center gap-3">
                                            <?php if(!empty($p['foto'])): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" 
                                                     alt="<?php echo $nombre; ?>" 
                                                     class="candidate-avatar shadow-sm">
                                            <?php else: ?>
                                                <div class="candidate-avatar-placeholder shadow-sm">
                                                    <?php echo $iniciales; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <h5 class="fw-bold mb-0 text-dark"><?php echo $nombre; ?></h5>
                                                    <span class="badge rounded-pill <?php echo $tipoBadge; ?> px-2 py-1" style="font-size: 0.72rem;">
                                                        <?php echo $tipoMiembro; ?>
                                                    </span>
                                                </div>
                                                <div class="text-muted small d-flex align-items-center gap-3 flex-wrap">
                                                    <span><i class="bi bi-briefcase me-1 text-secondary"></i><?php echo $profesion; ?></span>
                                                    <?php if($correo): ?>
                                                        <span><i class="bi bi-envelope me-1 text-secondary"></i><?php echo $correo; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opciones de Votación -->
                                        <div class="col-12 col-md-5">
                                            <div class="d-flex flex-column align-items-md-end gap-2">
                                                <!-- Status Badge de la tarjeta -->
                                                <span class="badge bg-light text-muted border px-3 py-1 rounded-pill small" id="status_badge_<?php echo $cedula; ?>">
                                                    <i class="bi bi-circle me-1"></i> Pendiente
                                                </span>

                                                <!-- Botones de Voto -->
                                                <div class="vote-options-group">
                                                    <!-- Opción ACEPTADO -->
                                                    <input type="radio" class="d-none radio-voto" 
                                                           name="votos[<?php echo $cedula; ?>]" 
                                                           id="voto_acc_<?php echo $cedula; ?>" 
                                                           value="ACEPTADO" 
                                                           required
                                                           onchange="actualizarEstadoVoto('<?php echo $cedula; ?>', 'ACEPTADO')">
                                                    <label class="btn-vote-option btn-vote-accept" 
                                                           id="label_acc_<?php echo $cedula; ?>" 
                                                           for="voto_acc_<?php echo $cedula; ?>">
                                                        <i class="bi bi-check-circle-fill fs-5"></i>
                                                        <span>ACEPTADO</span>
                                                    </label>

                                                    <!-- Opción RECHAZADO -->
                                                    <input type="radio" class="d-none radio-voto" 
                                                           name="votos[<?php echo $cedula; ?>]" 
                                                           id="voto_rej_<?php echo $cedula; ?>" 
                                                           value="RECHAZADO" 
                                                           required
                                                           onchange="actualizarEstadoVoto('<?php echo $cedula; ?>', 'RECHAZADO')">
                                                    <label class="btn-vote-option btn-vote-reject" 
                                                           id="label_rej_<?php echo $cedula; ?>" 
                                                           for="voto_rej_<?php echo $cedula; ?>">
                                                        <i class="bi bi-x-circle-fill fs-5"></i>
                                                        <span>RECHAZADO</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BARRA DE ACCIÓN INFERIOR -->
            <div class="actions-bar">
                <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span id="footerStatusIcon" class="text-warning fs-4"><i class="bi bi-exclamation-circle-fill"></i></span>
                        <div>
                            <div id="footerStatusTitle" class="fw-bold small text-dark">Boleta pendiente de completar</div>
                            <div class="text-muted small">Tu voto es anónimo, seguro y registrado en acta digital.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                        <a href="index.php?action=votaciones" class="btn btn-outline-secondary px-3 rounded-3">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-confirm-vote d-flex align-items-center gap-2" id="btnConfirmarVoto" onclick="validarVoto()">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>Confirmar Voto</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Captura Huella para Votación -->
    <div class="modal fade" id="modalHuella" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalHuellaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalHuellaLabel">
                        <i class="bi bi-fingerprint me-2"></i> Verificación Biométrica Requerida
                    </h5>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="estadoHuella" class="alert alert-info border-0 rounded-3 mb-4 small fw-semibold">
                        Por seguridad reglamentaria, autentique su voto colocando su huella dactilar registrada.
                    </div>

                    <div class="my-4">
                        <div class="fingerprint-wrapper shadow-sm">
                            <div class="fingerprint-pulse d-none" id="huellaPulse"></div>
                            <i class="bi bi-fingerprint text-primary" style="font-size: 4rem;" id="iconoHuella"></i>
                        </div>
                    </div>

                    <div class="progress mb-3 d-none rounded-pill" style="height: 8px;" id="progresoHuella">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                    </div>

                    <p class="text-muted small mb-0">Coloque firmemente el dedo índice sobre el sensor óptico.</p>
                </div>
                <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary px-4 rounded-3 fw-semibold shadow-sm" id="btnIniciarCaptura" onclick="iniciarCaptura()">
                        <i class="bi bi-play-fill me-1"></i> Iniciar Escaneo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN Y COMPROBANTE DE VOTO -->
    <?php if(isset($_GET['success']) && isset($_SESSION['ultimo_voto'])): 
        $uv = $_SESSION['ultimo_voto'];
        unset($_SESSION['ultimo_voto']);
    ?>
    <div class="modal fade" id="modalConfirmacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-success text-white border-0 py-3 d-print-none">
                    <h5 class="modal-title fw-bold" id="modalConfirmacionLabel">
                        <i class="bi bi-patch-check-fill me-2"></i> ¡Voto Registrado Exitosamente!
                    </h5>
                </div>
                <div class="modal-body p-4" id="printableArea">
                    <!-- Cabecera visible en impresión -->
                    <div class="d-none d-print-block text-center mb-3">
                        <img src="assets/img/logo_guataparo.png" alt="Logo" style="max-width: 120px;" class="mb-2">
                        <h5 class="fw-bold mb-0">GUATAPARO COUNTRY CLUB</h5>
                        <p class="small mb-0">Comprobante Oficial de Votación</p>
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    </div>

                    <div class="text-center mb-4 d-print-none">
                        <div class="display-3 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>
                        <h3 class="fw-bold text-dark">Sufragio Procesado</h3>
                        <p class="text-muted small">Su participación ha quedado asentada en el libro digital de votaciones.</p>
                    </div>

                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-6 col-12">
                            <label class="text-muted small text-uppercase fw-bold d-block">Socio Votante</label>
                            <span class="h6 fw-bold text-dark d-block mb-0"><?php echo htmlspecialchars($uv['socio']); ?></span>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="text-muted small text-uppercase fw-bold d-block">Acción</label>
                            <span class="badge bg-primary fs-6 px-3 py-1">#<?php echo htmlspecialchars($uv['accion']); ?></span>
                        </div>
                        <div class="col-md-3 col-6 text-md-end">
                            <label class="text-muted small text-uppercase fw-bold d-block">Fecha y Hora</label>
                            <span class="small fw-bold text-dark d-block"><?php echo $uv['fecha']; ?></span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-slate-800 mb-3">Resumen de Votación Emitida:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Candidato Postulado</th>
                                    <th class="text-center" style="width: 160px;">Decisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($uv['votos'] as $v): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($v['candidato']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $v['voto'] == 'ACEPTADO' ? 'success' : 'danger'; ?> px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-<?php echo $v['voto'] == 'ACEPTADO' ? 'check-circle' : 'x-circle'; ?> me-1"></i>
                                            <?php echo $v['voto']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-light border small text-center text-muted mb-0 d-print-none">
                        <i class="bi bi-shield-check text-success me-1"></i> Este comprobante certifica la validez legal del voto emitido.
                    </div>
                    
                    <div class="d-none d-print-block text-center mt-3">
                        <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                        <p class="small mb-0">¡Gracias por su participación!</p>
                        <p class="x-small text-muted" style="font-size: 8pt;"><?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 d-print-none">
                    <button type="button" class="btn btn-outline-primary rounded-3 font-weight-bold" onclick="imprimirComprobanteDirecto()">
                        <i class="bi bi-printer me-1"></i> Imprimir Comprobante
                    </button>
                    <a href="index.php?action=votaciones" class="btn btn-success px-4 rounded-3 fw-semibold">
                        <i class="bi bi-door-open me-1"></i> Finalizar y Salir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
            myModal.show();
        });

        // Impresión directa optimizada para tickera (80mm) y papel estándar
        function imprimirComprobanteDirecto() {
            const socio = <?php echo json_encode($uv['socio'] ?? ''); ?>;
            const accion = <?php echo json_encode($uv['accion'] ?? ''); ?>;
            const fecha = <?php echo json_encode($uv['fecha'] ?? ''); ?>;
            const votos = <?php echo json_encode($uv['votos'] ?? []); ?>;

            let filasVotos = '';
            votos.forEach(function(v) {
                const esAceptado = (v.voto === 'ACEPTADO');
                filasVotos += `
                    <tr>
                        <td style="padding: 5px 4px; font-weight: bold; border-bottom: 1px solid #ddd; text-align: left;">${v.candidato}</td>
                        <td style="padding: 5px 4px; text-align: center; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 8.5pt;">
                            <span style="display: inline-block; padding: 2px 6px; border: 1px solid #000; border-radius: 4px; ${esAceptado ? 'background: #e6f4ea;' : 'background: #fce8e6;'}">
                                ${v.voto}
                            </span>
                        </td>
                    </tr>
                `;
            });

            const ticketContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Comprobante de Votación</title>
                    <style>
                        @page {
                            size: 80mm auto;
                            margin: 2mm 3mm;
                        }
                        body {
                            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                            font-size: 9pt;
                            line-height: 1.35;
                            color: #000000;
                            margin: 0 auto;
                            padding: 4mm 2mm;
                            width: 74mm;
                            background: #ffffff;
                            box-sizing: border-box;
                        }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .divider {
                            border-top: 1px dashed #000;
                            margin: 8px 0;
                        }
                        .title {
                            font-size: 11pt;
                            font-weight: bold;
                            margin: 0;
                            text-transform: uppercase;
                        }
                        .subtitle {
                            font-size: 8.5pt;
                            margin-top: 2px;
                            color: #222;
                        }
                        .meta-table {
                            width: 100%;
                            margin: 6px 0;
                            font-size: 8.5pt;
                            border-collapse: collapse;
                        }
                        .meta-table td {
                            padding: 2px 0;
                            vertical-align: top;
                        }
                        .votes-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 8px 0;
                            font-size: 8.5pt;
                        }
                        .votes-table th {
                            border-bottom: 2px solid #000;
                            padding: 4px;
                            text-align: left;
                            font-size: 8pt;
                            text-transform: uppercase;
                        }
                        .footer-text {
                            font-size: 7.5pt;
                            color: #444;
                            margin-top: 8px;
                            text-align: center;
                        }
                    </style>
                </head>
                <body>
                    <div class="text-center">
                        <img src="assets/img/logo_guataparo.png" onerror="this.style.display='none'" style="max-width: 110px; height: auto; margin-bottom: 4px;" alt="Logo">
                        <div class="title">GUATAPARO COUNTRY CLUB</div>
                        <div class="subtitle">Comprobante Oficial de Votación</div>
                    </div>

                    <div class="divider"></div>

                    <table class="meta-table">
                        <tr>
                            <td style="color: #444; width: 40%;">SOCIO:</td>
                            <td style="font-weight: bold;">${socio}</td>
                        </tr>
                        <tr>
                            <td style="color: #444;">ACCIÓN:</td>
                            <td style="font-weight: bold;">#${accion}</td>
                        </tr>
                        <tr>
                            <td style="color: #444;">FECHA / HORA:</td>
                            <td style="font-weight: bold;">${fecha}</td>
                        </tr>
                    </table>

                    <div class="divider"></div>

                    <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px; text-transform: uppercase;">
                        Resumen de Votación:
                    </div>

                    <table class="votes-table">
                        <thead>
                            <tr>
                                <th>Candidato</th>
                                <th style="text-align: center; width: 85px;">Decisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filasVotos}
                        </tbody>
                    </table>

                    <div class="divider"></div>

                    <div class="footer-text">
                        <p style="margin: 2px 0; font-weight: bold;">¡Gracias por ejercer su derecho al voto!</p>
                        <p style="margin: 2px 0;">Constancia oficial generada por el Sistema Electoral.</p>
                        <p style="margin: 2px 0; font-family: monospace;">ID: GCC-VOTO-${Date.now().toString().slice(-6)}</p>
                    </div>
                </body>
                </html>
            `;

            let iframe = document.getElementById('ticketPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'ticketPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.top = '-9999px';
                iframe.style.left = '-9999px';
                iframe.style.width = '0px';
                iframe.style.height = '0px';
                iframe.style.border = 'none';
                document.body.appendChild(iframe);
            }

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(ticketContent);
            doc.close();

            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 300);
        }
    </script>
    <?php endif; ?>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
    <script>
        let cedulaVotante = '';
        const totalCandidatos = <?php echo $totalCandidatos; ?>;
        const modalHuellaEl = document.getElementById('modalHuella');
        const modalHuella = modalHuellaEl ? new bootstrap.Modal(modalHuellaEl) : null;

        // Actualizar UI cuando el usuario vota por un candidato
        function actualizarEstadoVoto(cedula, valor) {
            const card = document.getElementById(`card_${cedula}`);
            const badge = document.getElementById(`status_badge_${cedula}`);
            const labelAcc = document.getElementById(`label_acc_${cedula}`);
            const labelRej = document.getElementById(`label_rej_${cedula}`);

            if (valor === 'ACEPTADO') {
                card.classList.remove('voted-rechazado');
                card.classList.add('voted-aceptado');
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small';
                badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ACEPTADO';

                labelAcc.classList.add('active');
                labelRej.classList.remove('active');
            } else if (valor === 'RECHAZADO') {
                card.classList.remove('voted-aceptado');
                card.classList.add('voted-rechazado');
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill small';
                badge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> RECHAZADO';

                labelRej.classList.add('active');
                labelAcc.classList.remove('active');
            }

            actualizarProgresoGeneral();
        }

        // Calcular progreso general y actualizar barra
        function actualizarProgresoGeneral() {
            const totalVotados = document.querySelectorAll('input.radio-voto:checked').length;
            const porcentaje = totalCandidatos > 0 ? Math.round((totalVotados / totalCandidatos) * 100) : 0;

            const progressBar = document.getElementById('progresoBarra');
            const progressText = document.getElementById('progresoTexto');
            const footerTitle = document.getElementById('footerStatusTitle');
            const footerIcon = document.getElementById('footerStatusIcon');

            if (progressBar) {
                progressBar.style.width = `${porcentaje}%`;
                progressBar.setAttribute('aria-valuenow', porcentaje);
            }

            if (progressText) {
                progressText.textContent = `${totalVotados} de ${totalCandidatos} candidatos completados`;
                if (totalVotados === totalCandidatos && totalCandidatos > 0) {
                    progressText.className = 'badge bg-success rounded-pill';
                } else {
                    progressText.className = 'badge bg-secondary rounded-pill';
                }
            }

            if (footerTitle && footerIcon) {
                if (totalVotados === totalCandidatos && totalCandidatos > 0) {
                    footerTitle.innerHTML = '<span class="text-success fw-bold">✓ Boleta completada al 100%</span>';
                    footerIcon.className = 'text-success fs-4';
                    footerIcon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                } else {
                    const restantes = totalCandidatos - totalVotados;
                    footerTitle.innerHTML = `Faltan <span class="fw-bold text-danger">${restantes}</span> candidato${restantes > 1 ? 's' : ''} por votar`;
                    footerIcon.className = 'text-warning fs-4';
                    footerIcon.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>';
                }
            }
        }

        // Acciones masivas: Votar a todos
        function votarATodos(decision) {
            const radios = document.querySelectorAll(`input.radio-voto[value="${decision}"]`);
            radios.forEach(radio => {
                radio.checked = true;
                const match = radio.id.match(/^voto_(?:acc|rej)_(.+)$/);
                if (match && match[1]) {
                    actualizarEstadoVoto(match[1], decision);
                }
            });
        }

        // Limpiar votos
        function limpiarVotos() {
            const radios = document.querySelectorAll('input.radio-voto');
            radios.forEach(radio => radio.checked = false);

            document.querySelectorAll('.candidate-vote-card').forEach(card => {
                card.classList.remove('voted-aceptado', 'voted-rechazado');
            });

            document.querySelectorAll('[id^="status_badge_"]').forEach(badge => {
                badge.className = 'badge bg-light text-muted border px-3 py-1 rounded-pill small';
                badge.innerHTML = '<i class="bi bi-circle me-1"></i> Pendiente';
            });

            document.querySelectorAll('.btn-vote-option').forEach(btn => {
                btn.classList.remove('active');
            });

            actualizarProgresoGeneral();
        }

        // Actualizar detalle de la acción seleccionada
        function actualizarDetalleAccion(selectEl) {
            const pill = document.getElementById('accionPill');
            if (!pill) return;

            if (selectEl.value && selectEl.selectedIndex >= 0) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const display = opt.dataset.display || opt.text;
                const cedula = opt.dataset.cedula || '';

                pill.className = 'p-3 bg-primary-subtle border border-primary-subtle rounded-3 text-start';
                pill.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-check-fill text-primary fs-5"></i>
                        <div>
                            <strong class="text-dark d-block">${display}</strong>
                            <small class="text-muted">Cédula: ${cedula}</small>
                        </div>
                    </div>
                `;
            } else {
                pill.className = 'p-3 bg-light border rounded-3 text-center text-muted small';
                pill.innerHTML = `
                    <i class="bi bi-person-bounding-box fs-4 d-block mb-1 text-secondary"></i>
                    <span>Ninguna acción seleccionada</span>
                `;
            }
        }

        // Validar antes de abrir modal de huella
        function validarVoto() {
            const selectAccion = document.getElementById('accion');
            const hiddenAccion = document.getElementById('input_accion');
            
            let accionVal = '';
            
            if (selectAccion) {
                accionVal = selectAccion.value;
                if (accionVal) {
                    cedulaVotante = selectAccion.options[selectAccion.selectedIndex].dataset.cedula;
                }
            } else if (hiddenAccion) {
                accionVal = hiddenAccion.value;
                cedulaVotante = hiddenAccion.dataset.cedula;
            }

            if (!accionVal) {
                alert('⚠️ Por favor seleccione su número de acción o cédula para iniciar el proceso de votación.');
                if (selectAccion) selectAccion.focus();
                return;
            }

            const radiosChecked = document.querySelectorAll('input.radio-voto:checked');
            if (radiosChecked.length < totalCandidatos) {
                const faltantes = totalCandidatos - radiosChecked.length;
                alert(`⚠️ Atención: Debe votar por todos los candidatos de la boleta electoral.\nFaltan ${faltantes} candidato(s) por decidir.`);
                
                // Desplazar al primer candidato sin votar
                const allCards = document.querySelectorAll('.candidate-vote-card');
                for (let card of allCards) {
                    if (!card.classList.contains('voted-aceptado') && !card.classList.contains('voted-rechazado')) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        card.style.transition = 'box-shadow 0.3s ease';
                        card.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.4)';
                        setTimeout(() => card.style.boxShadow = '', 2000);
                        break;
                    }
                }
                return;
            }

            // Preparar modal biométrico
            document.getElementById('estadoHuella').className = 'alert alert-info border-0 rounded-3 mb-4 small fw-semibold';
            document.getElementById('estadoHuella').innerHTML = 'Por seguridad reglamentaria, autentique su voto colocando su huella dactilar registrada.';
            document.getElementById('iconoHuella').className = 'bi bi-fingerprint text-primary';
            document.getElementById('huellaPulse').classList.add('d-none');
            document.getElementById('btnIniciarCaptura').disabled = false;
            
            modalHuella.show();
        }

        // Captura biométrica
        async function iniciarCaptura() {
            const estado = document.getElementById('estadoHuella');
            const icono = document.getElementById('iconoHuella');
            const progreso = document.getElementById('progresoHuella');
            const pulse = document.getElementById('huellaPulse');
            const btnIniciar = document.getElementById('btnIniciarCaptura');
            
            btnIniciar.disabled = true;
            estado.innerHTML = '<strong>⏳ Sensor activo:</strong> Coloque su dedo en el lector óptico y manténgalo presionado...';
            estado.className = 'alert alert-warning border-0 rounded-3 mb-4 small fw-semibold';
            progreso.classList.remove('d-none');
            pulse.classList.remove('d-none');
            icono.className = 'bi bi-fingerprint text-warning';
            
            try {
                const driver = '<?php echo $huellaConfig["driver"]; ?>';
                const ip = '<?php echo $huellaConfig["hik_ip"]; ?>';
                const port = '<?php echo $huellaConfig["hik_port"]; ?>';
                const user = '<?php echo $huellaConfig["hik_user"]; ?>';
                const pass = '<?php echo $huellaConfig["hik_pass"]; ?>';

                // 1. Capturar huella desde el bridge
                const url = `http://localhost:8888/capture?driver=${driver}&ip=${ip}&port=${port}&user=${user}&pass=${pass}&mode=verify`;
                const response = await fetch(url);
                const data = await response.json();

                if (data.status === 'success') {
                    const capturedTemplate = data.template;
                    estado.innerHTML = '<strong>⏳ Analizando:</strong> Verificando biometría contra la base de datos segura...';
                    
                    // 2. Obtener template guardado
                    const getStoredResponse = await fetch('index.php?action=huella_verify', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ cedula: cedulaVotante })
                    });
                    
                    const storedData = await getStoredResponse.json();
                    
                    if (!storedData.success) {
                        throw new Error(storedData.message || 'No se pudo recuperar la huella del servidor.');
                    }

                    // 3. Comparar huellas usando el Bridge
                    const bridgeMatchUrl = `http://localhost:8888/verify`;
                    let bestMatch = false;
                    const storedTemplates = Array.isArray(storedData.stored_template) ? storedData.stored_template : [storedData.stored_template];
                    
                    for (const tpl of storedTemplates) {
                        try {
                            const matchResponse = await fetch(bridgeMatchUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    template1: capturedTemplate,
                                    template2: tpl
                                })
                            });
                            
                            const matchData = await matchResponse.json();
                            if (matchData.match === true) {
                                bestMatch = true;
                                break;
                            }
                        } catch (e) {
                            console.warn("Error comparando template:", e);
                        }
                    }
                    
                    if (bestMatch === true) {
                        estado.innerHTML = '<strong>✓ Identidad Verificada:</strong> Procesando su boleta electoral...';
                        estado.className = 'alert alert-success border-0 rounded-3 mb-4 small fw-semibold';
                        icono.className = 'bi bi-fingerprint text-success';
                        
                        setTimeout(() => {
                            document.getElementById('formVotacion').submit();
                        }, 1000);
                    } else {
                        throw new Error('La huella capturada no coincide con las huellas registradas del socio.');
                    }
                } else {
                    throw new Error(data.message || 'Fallo en la captura de la huella.');
                }
            } catch (error) {
                console.error('Error Biometría:', error);
                estado.innerHTML = '<strong>✗ Error de Verificación:</strong><br>' + error.message;
                estado.className = 'alert alert-danger border-0 rounded-3 mb-4 small fw-semibold';
                icono.className = 'bi bi-fingerprint text-danger';
                btnIniciar.disabled = false;
                
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    estado.innerHTML = '<strong>✗ Error de Conexión:</strong><br>No se pudo conectar al lector biométrico. Verifique que el servicio Python Bridge esté iniciado.';
                }
            } finally {
                progreso.classList.add('d-none');
                pulse.classList.add('d-none');
            }
        }

        // Inicializar listeners al cargar
        document.addEventListener('DOMContentLoaded', () => {
            const selectAccion = document.getElementById('accion');
            if (selectAccion) {
                actualizarDetalleAccion(selectAccion);
            }
            actualizarProgresoGeneral();
        });
    </script>
</body>
</html>
