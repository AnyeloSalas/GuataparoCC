<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['tipousuario'] != 'Administrador') {
    header("Location: index.php?action=votaciones");
    exit();
}

$iniciov_val = (!empty($fechas['iniciov'])) ? $fechas['iniciov'] : date('Y-m-d');
$finv_val = (!empty($fechas['finv'])) ? $fechas['finv'] : date('Y-m-d', strtotime('+7 days'));
$porcentaje_val = isset($porcentaje_aceptacion) ? (int)$porcentaje_aceptacion : 50;
$minimo_val = isset($minimo_votantes) ? (int)$minimo_votantes : 0;

// Determinar estado actual de la votación
$hoy = date('Y-m-d');
$estado_votacion = 'inactiva';
$badge_estado = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><i class="bi bi-pause-circle me-1"></i> Sin Período Activo</span>';

if (!empty($fechas['iniciov']) && !empty($fechas['finv'])) {
    if ($hoy < $fechas['iniciov']) {
        $badge_estado = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill"><i class="bi bi-clock-history me-1"></i> Programada (Inicia el ' . date('d/m/Y', strtotime($fechas['iniciov'])) . ')</span>';
    } elseif ($hoy >= $fechas['iniciov'] && $hoy <= $fechas['finv']) {
        $badge_estado = '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2 rounded-pill"><i class="bi bi-broadcast me-1"></i> En Curso (Hasta el ' . date('d/m/Y', strtotime($fechas['finv'])) . ')</span>';
    } else {
        $badge_estado = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i class="bi bi-calendar-x me-1"></i> Finalizada (' . date('d/m/Y', strtotime($fechas['finv'])) . ')</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Votaciones - Sistema Guataparo</title>
    <link href="assets/vendor/css/bootstrap5.min.css" rel="stylesheet">
    <link href="assets/vendor/css/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #172554;
            --primary-light: #eff6ff;
            --card-border: #e2e8f0;
            --bg-body: #f8fafc;
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

        /* Cards */
        .config-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.2s ease-in-out;
        }

        .config-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            background-color: #ffffff;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .header-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.25rem;
        }

        /* Parameter widget inputs */
        .param-widget {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .param-widget:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .param-widget label {
            font-size: 0.825rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .param-widget .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: 600;
            color: #0f172a;
            padding: 0.6rem 0.85rem;
        }

        .param-widget .form-control:focus {
            box-shadow: none;
            border-color: #2563eb;
        }

        /* Postulado Card */
        .candidate-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
            height: 100%;
        }

        .candidate-card:hover {
            border-color: #93c5fd;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.08);
        }

        .candidate-card.is-selected {
            border-color: #2563eb;
            background-color: #f8faff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
        }

        .candidate-card .candidate-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            transition: border-color 0.2s;
        }

        .candidate-card.is-selected .candidate-avatar {
            border-color: #2563eb;
        }

        .candidate-avatar-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #ffffff;
            background: linear-gradient(135deg, #64748b, #475569);
        }

        .candidate-card.is-selected .candidate-avatar-placeholder {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        /* Custom Switch Indicator */
        .candidate-check-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }

        .candidate-card.is-selected .candidate-check-badge {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        /* Danger Zone Card */
        .danger-zone-card {
            border: 1px dashed #fca5a5;
            background-color: #fffafb;
            border-radius: 16px;
        }

        /* Floating action footer */
        .actions-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            position: sticky;
            bottom: 0;
            z-index: 1020;
            padding: 1rem 0;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.05);
        }

        .btn-primary-custom {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.75rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .nav-link-btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 0.45rem 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
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
        
        <!-- Header Banner -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div class="header-icon bg-primary text-white shadow-sm">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-0 text-slate-900">Configuración del Proceso Electoral</h2>
                </div>
                <p class="text-muted small mb-0 ps-md-5">
                    Define las fechas oficiales, quórum reglamentario y selecciona los candidatos autorizados a participar.
                </p>
            </div>
            <div>
                <?php echo $badge_estado; ?>
            </div>
        </div>

        <!-- Feedback Alerts -->
        <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1 fw-semibold"><?php echo htmlspecialchars($success); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1 fw-semibold"><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="formConfiguracion" action="index.php?action=votaciones_configuracion" method="POST">
            <?php echo Csrf::tokenField(); ?>

            <!-- SECCIÓN 1: CRONOGRAMA Y PARÁMETROS -->
            <div class="config-card mb-4">
                <div class="config-card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-calendar2-range fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">1. Cronograma y Parámetros de Validez</h5>
                            <small class="text-muted">Establece el rango de fechas de sufragio y las reglas de aprobación</small>
                        </div>
                    </div>
                    <span id="duracionBadge" class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                        <i class="bi bi-clock me-1"></i> Calculando período...
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Fecha Inicio -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="param-widget">
                                <label for="iniciov">
                                    <i class="bi bi-calendar-event text-primary"></i> Inicio Votación *
                                </label>
                                <input type="date" class="form-control" id="iniciov" name="iniciov" 
                                       value="<?php echo htmlspecialchars($iniciov_val); ?>" required>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Apertura del sufragio
                                </div>
                            </div>
                        </div>

                        <!-- Fecha Fin -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="param-widget">
                                <label for="finv">
                                    <i class="bi bi-calendar-check text-success"></i> Fin Votación *
                                </label>
                                <input type="date" class="form-control" id="finv" name="finv" 
                                       value="<?php echo htmlspecialchars($finv_val); ?>" required>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Cierre definitivo
                                </div>
                            </div>
                        </div>

                        <!-- Aceptación (%) -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="param-widget">
                                <label for="porcentaje_aceptacion">
                                    <i class="bi bi-percent text-info"></i> Aceptación Requerida *
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="porcentaje_aceptacion" name="porcentaje_aceptacion" 
                                           value="<?php echo $porcentaje_val; ?>" min="1" max="100" required>
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-check2-circle me-1"></i> Mínimo de votos a favor
                                </div>
                            </div>
                        </div>

                        <!-- Quórum Mínimo -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="param-widget">
                                <label for="minimo_votantes">
                                    <i class="bi bi-people text-warning"></i> Quórum Mínimo *
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minimo_votantes" name="minimo_votantes" 
                                           value="<?php echo $minimo_val; ?>" min="0" required>
                                    <span class="input-group-text bg-light text-muted">Votantes</span>
                                </div>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-person-check me-1"></i> Participación mínima
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje informativo de ayuda -->
                    <div class="alert alert-light border border-info-subtle bg-info-subtle text-info-emphasis rounded-3 mt-4 mb-0 d-flex align-items-center gap-3">
                        <i class="bi bi-lightbulb-fill fs-4 text-info"></i>
                        <div class="small">
                            <strong>Recomendación reglamentaria:</strong> La fecha de inicio de votación usualmente se limita a una semana para garantizar la concentración y el seguimiento transparente del proceso electoral.
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: SELECCIÓN DE POSTULADOS -->
            <div class="config-card mb-4">
                <div class="config-card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-3">
                                <i class="bi bi-person-check fs-5"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0">2. Candidatos Postulados Habilitados</h5>
                                <small class="text-muted">Selecciona los miembros que figurarán en el tarjetón de votación</small>
                            </div>
                        </div>

                        <!-- Controles rápidos y filtros -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span id="contadorSeleccionados" class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                                0 seleccionados
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                                <i class="bi bi-check-all me-1"></i> Marcar Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                                <i class="bi bi-x-lg me-1"></i> Desmarcar Todos
                            </button>
                        </div>
                    </div>

                    <!-- Filtro buscador de candidatos -->
                    <div class="row mt-3 pt-3 border-top g-2 align-items-center">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="filtroPostulados" class="form-control border-start-0" 
                                       placeholder="Buscar por nombre, cédula o profesión...">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end gap-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="filtroTipo" id="filtroTodos" value="todos" checked>
                                <label class="btn btn-outline-secondary" for="filtroTodos">Todos</label>

                                <input type="radio" class="btn-check" name="filtroTipo" id="filtroPrincipales" value="principal">
                                <label class="btn btn-outline-secondary" for="filtroPrincipales">Principales</label>

                                <input type="radio" class="btn-check" name="filtroTipo" id="filtroSuplentes" value="suplente">
                                <label class="btn btn-outline-secondary" for="filtroSuplentes">Suplentes</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if(!empty($postulados)): ?>
                        <div class="row g-3" id="postuladosGrid">
                            <?php foreach($postulados as $p): 
                                $isSelected = !empty($p['en_votacion']);
                                $cedula = htmlspecialchars($p['cedula']);
                                $nombre = htmlspecialchars($p['nombre'] ?? trim(($p['primer_nombre'] ?? '') . ' ' . ($p['primer_apellido'] ?? '')));
                                $profesion = htmlspecialchars($p['profesion'] ?? 'Sin profesión especificada');
                                $tipoMiembro = strtoupper($p['tipomiembro'] ?? 'PRINCIPAL');
                                $badgeTipoClass = ($tipoMiembro === 'PRINCIPAL') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                
                                // Iniciales para avatar fallback
                                $palabras = explode(' ', $nombre);
                                $iniciales = '';
                                if (!empty($palabras[0])) $iniciales .= mb_substr($palabras[0], 0, 1);
                                if (!empty($palabras[1])) $iniciales .= mb_substr($palabras[1], 0, 1);
                                $iniciales = strtoupper($iniciales) ?: 'P';
                            ?>
                            <div class="col-12 col-md-6 col-lg-4 candidate-item" 
                                 data-nombre="<?php echo strtolower($nombre); ?>" 
                                 data-cedula="<?php echo strtolower($cedula); ?>" 
                                 data-profesion="<?php echo strtolower($profesion); ?>"
                                 data-tipo="<?php echo strtolower($tipoMiembro); ?>">
                                
                                <div class="candidate-card <?php echo $isSelected ? 'is-selected' : ''; ?>" onclick="togglePostuladoCard(this)">
                                    <input type="checkbox" class="d-none postulado-checkbox" 
                                           name="postulados_seleccionados[]" 
                                           value="<?php echo $cedula; ?>"
                                           <?php echo $isSelected ? 'checked' : ''; ?>>
                                    
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if(!empty($p['foto'])): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" 
                                                     alt="<?php echo $nombre; ?>" 
                                                     class="candidate-avatar">
                                            <?php else: ?>
                                                <div class="candidate-avatar-placeholder">
                                                    <?php echo $iniciales; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 170px;" title="<?php echo $nombre; ?>">
                                                    <?php echo $nombre; ?>
                                                </div>
                                                <div class="text-muted small d-flex align-items-center gap-1">
                                                    <i class="bi bi-card-text text-secondary"></i>
                                                    <span>C.I. <?php echo $cedula; ?></span>
                                                </div>
                                                <div class="text-muted small text-truncate" style="max-width: 170px;" title="<?php echo $profesion; ?>">
                                                    <i class="bi bi-briefcase text-secondary me-1"></i><?php echo $profesion; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column align-items-end justify-content-between gap-2">
                                            <span class="candidate-check-badge">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                            <span class="badge rounded-pill <?php echo $badgeTipoClass; ?>" style="font-size: 0.7rem;">
                                                <?php echo $tipoMiembro; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Estado vacío para filtro de búsqueda -->
                        <div id="noMatchMessage" class="text-center py-5 d-none">
                            <i class="bi bi-search fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No se encontraron postulados con ese criterio de búsqueda.</p>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <span class="header-icon bg-warning-subtle text-warning fs-3 p-3">
                                    <i class="bi bi-people"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold">No hay candidatos postulados registrados</h5>
                            <p class="text-muted small">Debes registrar candidatos antes de poder seleccionarlos para el proceso de votación.</p>
                            <a href="index.php?action=postulados_create" class="btn btn-primary btn-sm rounded-pill px-4">
                                <i class="bi bi-plus-circle me-1"></i> Registrar Postulados
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BARRA DE ACCIONES INFERIOR -->
            <div class="actions-bar mb-4">
                <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-primary"></i>
                        <span>Los cambios surtirán efecto inmediatamente en las votaciones activas.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                        <a href="index.php?action=votaciones" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary-custom d-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>Guardar Configuración</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- SECCIÓN 3: GESTIÓN Y REINICIO DE VOTOS (ZONA DE CONTROL) -->
        <div class="danger-zone-card p-4 mt-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="header-icon bg-danger text-white rounded-3 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-danger mb-1">Zona de Control - Reinicio de Votos</h6>
                        <p class="text-muted small mb-0">
                            Elimina únicamente los votos emitidos <strong>dentro del período activo</strong> (<?php echo date('d/m/Y', strtotime($iniciov_val)); ?> al <?php echo date('d/m/Y', strtotime($finv_val)); ?>).
                            Útil para fases de prueba o reinicio del evento electoral.
                        </p>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm text-nowrap fw-semibold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalLimpiarVotos">
                    <i class="bi bi-trash3 me-1"></i> Eliminar Votos del Período
                </button>
            </div>
        </div>

    </div>

    <!-- Modal de Confirmación para Reiniciar Votos -->
    <div class="modal fade" id="modalLimpiarVotos" tabindex="-1" aria-labelledby="modalLimpiarVotosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalLimpiarVotosLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación de Votos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark mb-3">
                        ¿Está completamente seguro de que desea eliminar <strong>TODOS</strong> los votos registrados entre el 
                        <span class="badge bg-light text-dark border"><?php echo date('d/m/Y', strtotime($iniciov_val)); ?></span> y el 
                        <span class="badge bg-light text-dark border"><?php echo date('d/m/Y', strtotime($finv_val)); ?></span>?
                    </p>
                    <div class="alert alert-warning border-0 small d-flex gap-2 align-items-center mb-0">
                        <i class="bi bi-shield-slash fs-4 text-warning"></i>
                        <span>Esta acción es irreversible y no se puede deshacer. Los sufragios emitidos en este rango serán purgados.</span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <form action="index.php?action=votaciones_configuracion" method="POST" class="d-inline">
                        <?php echo Csrf::tokenField(); ?>
                        <input type="hidden" name="accion_config" value="limpiar_votos">
                        <button type="submit" class="btn btn-danger rounded-3 fw-semibold">
                            <i class="bi bi-trash3 me-1"></i> Sí, Eliminar Definitivamente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/js/bootstrap5.bundle.min.js"></script>
    <script>
        // Toggle card postulado al hacer click
        function togglePostuladoCard(cardElement) {
            const checkbox = cardElement.querySelector('.postulado-checkbox');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                cardElement.classList.add('is-selected');
            } else {
                cardElement.classList.remove('is-selected');
            }
            actualizarContadorPostulados();
        }

        // Actualizar contador badge
        function actualizarContadorPostulados() {
            const total = document.querySelectorAll('.postulado-checkbox').length;
            const seleccionados = document.querySelectorAll('.postulado-checkbox:checked').length;
            const badge = document.getElementById('contadorSeleccionados');
            if (badge) {
                badge.textContent = `${seleccionados} de ${total} seleccionados`;
                if (seleccionados > 0) {
                    badge.classList.remove('bg-secondary');
                    badge.classList.add('bg-primary');
                } else {
                    badge.classList.remove('bg-primary');
                    badge.classList.add('bg-secondary');
                }
            }
        }

        // Marcar y Desmarcar todos
        document.getElementById('btnSelectAll')?.addEventListener('click', function() {
            const items = document.querySelectorAll('.candidate-item');
            items.forEach(item => {
                // Solo si no está oculto por filtros
                if (item.style.display !== 'none') {
                    const card = item.querySelector('.candidate-card');
                    const checkbox = item.querySelector('.postulado-checkbox');
                    checkbox.checked = true;
                    card.classList.add('is-selected');
                }
            });
            actualizarContadorPostulados();
        });

        document.getElementById('btnDeselectAll')?.addEventListener('click', function() {
            const items = document.querySelectorAll('.candidate-item');
            items.forEach(item => {
                if (item.style.display !== 'none') {
                    const card = item.querySelector('.candidate-card');
                    const checkbox = item.querySelector('.postulado-checkbox');
                    checkbox.checked = false;
                    card.classList.remove('is-selected');
                }
            });
            actualizarContadorPostulados();
        });

        // Filtrado en vivo de postulados
        const inputFiltro = document.getElementById('filtroPostulados');
        const radiosTipo = document.querySelectorAll('input[name="filtroTipo"]');

        function aplicarFiltros() {
            const termino = inputFiltro ? inputFiltro.value.toLowerCase().trim() : '';
            const tipoSeleccionado = document.querySelector('input[name="filtroTipo"]:checked')?.value || 'todos';
            const items = document.querySelectorAll('.candidate-item');
            let visibles = 0;

            items.forEach(item => {
                const nombre = item.dataset.nombre || '';
                const cedula = item.dataset.cedula || '';
                const profesion = item.dataset.profesion || '';
                const tipo = item.dataset.tipo || '';

                const coincideTexto = !termino || nombre.includes(termino) || cedula.includes(termino) || profesion.includes(termino);
                const coincideTipo = (tipoSeleccionado === 'todos') || (tipo === tipoSeleccionado);

                if (coincideTexto && coincideTipo) {
                    item.style.display = '';
                    visibles++;
                } else {
                    item.style.display = 'none';
                }
            });

            const noMatch = document.getElementById('noMatchMessage');
            if (noMatch) {
                noMatch.classList.toggle('d-none', visibles > 0 || items.length === 0);
            }
        }

        inputFiltro?.addEventListener('input', aplicarFiltros);
        radiosTipo.forEach(radio => radio.addEventListener('change', aplicarFiltros));

        // Calcular y validar rango de fechas dinámicamente
        const inputInicio = document.getElementById('iniciov');
        const inputFin = document.getElementById('finv');
        const badgeDuracion = document.getElementById('duracionBadge');

        function calcularDuracion() {
            if (!inputInicio || !inputFin || !badgeDuracion) return;

            const inicio = new Date(inputInicio.value);
            const fin = new Date(inputFin.value);

            if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                badgeDuracion.className = 'badge bg-light text-dark border px-3 py-2 rounded-pill small';
                badgeDuracion.innerHTML = '<i class="bi bi-clock me-1"></i> Fechas incompletas';
                return;
            }

            const diffTime = fin - inicio;
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24)) + 1; // inclusivo

            if (diffDays < 1) {
                badgeDuracion.className = 'badge bg-danger text-white border-0 px-3 py-2 rounded-pill small';
                badgeDuracion.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> La fecha de fin debe ser posterior al inicio';
                inputFin.setCustomValidity('La fecha de fin no puede ser anterior a la de inicio.');
            } else {
                badgeDuracion.className = 'badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small';
                badgeDuracion.innerHTML = `<i class="bi bi-calendar-check me-1"></i> Duración: ${diffDays} día${diffDays > 1 ? 's' : ''}`;
                inputFin.setCustomValidity('');
            }
        }

        inputInicio?.addEventListener('change', calcularDuracion);
        inputFin?.addEventListener('change', calcularDuracion);

        // Inicializar componentes al cargar
        document.addEventListener('DOMContentLoaded', () => {
            actualizarContadorPostulados();
            calcularDuracion();
        });
    </script>
</body>
</html>
