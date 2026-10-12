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

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sistema de Votaciones</h1>
    <?php if(!isset($_SESSION['tipousuario']) || strtoupper($_SESSION['tipousuario']) !== 'SOCIO'): ?>
    <a href="index.php?action=votaciones_historico" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-history fa-sm text-white-50 mr-1"></i> Ver Histórico
    </a>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ¡Voto registrado exitosamente!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 'fuera_periodo'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        No se encuentra en período de votación.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 'sin_postulados'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> No se puede finalizar el proceso de votación porque no hay candidatos postulados. Debe agregar postulados primero.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php $es_socio_view = (isset($_SESSION['tipousuario']) && strtoupper($_SESSION['tipousuario']) === 'SOCIO'); ?>

<?php if(!$es_socio_view): ?>
<!-- Estadísticas Slim -->
<div class="row g-2 mb-3">
    <div class="col-xl-2 col-md-4 mb-2">
        <div class="card border-left-primary shadow-sm h-100 py-2">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Votos</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_votos']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-2">
        <div class="card border-left-success shadow-sm h-100 py-2">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Votantes</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['votantes_unicos']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-2">
        <div class="card border-left-info shadow-sm h-100 py-2">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Socios</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_socios']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 mb-2">
        <div class="card border-left-warning shadow-sm h-100 py-2">
            <div class="card-body py-2 px-3">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Participación</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['participacion']; ?>%</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Período de Votación y Candidatos Compacto -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="row align-items-center">
            <div class="col-md-5 border-right">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-primary"><i class="fas fa-calendar-alt fa-2x opacity-25"></i></div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Período de Votación</div>
                        <div class="small">
                            <strong>Inicio:</strong> <?php echo !empty($fechas['iniciov']) ? date('d/m/y', strtotime($fechas['iniciov'])) : '-'; ?> |
                            <strong>Fin:</strong> <?php echo !empty($fechas['finv']) ? date('d/m/y', strtotime($fechas['finv'])) : '-'; ?>
                        </div>
                        <?php if($enPeriodo): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 0.65rem;"><i class="fas fa-check-circle me-1"></i> ACTIVO</span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;"><i class="fas fa-clock me-1"></i> FUERA DE PERÍODO</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-7 ps-3">
                <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.65rem;">Candidatos (<?php echo count($postulados_en_votacion); ?>)</div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach($postulados_en_votacion as $p): ?>
                        <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 border shadow-xs" style="font-size: 0.75rem;">
                             <?php if(!empty($p['foto'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($p['foto']); ?>" class="rounded-circle me-1" style="width: 20px; height: 20px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-1" style="width: 20px; height: 20px;"><i class="fas fa-user text-white" style="font-size: 0.5rem;"></i></div>
                            <?php endif; ?>
                            <span class="fw-bold text-dark pe-1"><?php echo htmlspecialchars(explode(' ', $p['nombre'] ?? $p['primer_nombre'])[0]); ?></span>
                            <span class="badge <?php echo strtoupper($p['tipomiembro']) == 'PRINCIPAL' ? 'bg-primary' : 'bg-warning text-dark'; ?> rounded-pill" style="font-size: 0.6rem;"><?php echo substr($p['tipomiembro'],0,1); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Compactas -->
<div class="row g-2 mb-3 text-center">
    <div class="col-lg-3 col-md-6">
        <a href="index.php?action=votaciones_votar" class="card shadow-sm h-100 text-decoration-none border-0 border-top border-4 border-success hover-lift">
            <div class="card-body p-3">
                <i class="fas fa-vote-yea fa-2x text-success mb-2"></i>
                <h6 class="mb-0 fw-bold text-dark">Votar</h6>
                <div class="small text-muted"><?php echo $enPeriodo ? 'Ir a votar' : 'Fuera de plazo'; ?></div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6">
        <a href="index.php?action=votaciones_postulados" class="card shadow-sm h-100 text-decoration-none border-0 border-top border-4 border-primary hover-lift">
            <div class="card-body p-3">
                <i class="fas fa-users-list fa-2x text-primary mb-2"></i>
                <h6 class="mb-0 fw-bold text-dark">Postulados</h6>
                <div class="small text-muted">Ver candidatos</div>
            </div>
        </a>
    </div>
    <?php if(!$es_socio_view): ?>
    <div class="col-lg-3 col-md-6">
        <a href="index.php?action=votaciones_resultados" class="card shadow-sm h-100 text-decoration-none border-0 border-top border-4 border-warning hover-lift">
            <div class="card-body p-3">
                <i class="fas fa-chart-bar fa-2x text-warning mb-2"></i>
                <h6 class="mb-0 fw-bold text-dark">Resultados</h6>
                <div class="small text-muted">Ver parciales</div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6">
        <a href="index.php?action=votaciones_historico" class="card shadow-sm h-100 text-decoration-none border-0 border-top border-4 border-info hover-lift">
            <div class="card-body p-3">
                <i class="fas fa-history fa-2x text-info mb-2"></i>
                <h6 class="mb-0 fw-bold text-dark">Histórico</h6>
                <div class="small text-muted">Consultar pasado</div>
            </div>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if(isset($_GET['success']) && $_GET['success'] == 'finalizado'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>¡Votación Finalizada!</strong> Los candidatos han sido retirados de la vista de votación actual.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($_SESSION['tipousuario'] == 'Administrador'): ?>
<div class="card shadow mb-4 border-bottom-danger">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-cogs"></i> Administración</h6>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?action=votaciones_configuracion" class="btn btn-danger mr-2 mb-2">
                <i class="fas fa-calendar-check mr-1"></i> Configurar Votación
            </a>
            <a href="index.php?action=votaciones_tokens" class="btn btn-primary mr-2 mb-2">
                <i class="fas fa-link mr-1"></i> Generar Tokens Remotos
            </a>
            <?php if(!empty($postulados_en_votacion)): ?>
            <button type="button" class="btn btn-outline-danger mb-2" data-toggle="modal" data-target="#modalFinalizar">
                <i class="fas fa-flag-checkered mr-1"></i> Finalizar Proceso Activo
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-outline-danger mb-2 disabled" disabled title="No hay postulados en la votación actual">
                <i class="fas fa-flag-checkered mr-1"></i> Finalizar Proceso Activo
            </button>
            <?php endif; ?>
        </div>
        <?php if(empty($postulados_en_votacion)): ?>
        <div class="mt-3 small text-warning">
            <i class="fas fa-exclamation-triangle"></i> No se puede finalizar el proceso porque no hay candidatos postulados en la votación actual. Debe agregar postulados primero.
        </div>
        <?php else: ?>
        <div class="mt-3 small text-muted">
            <i class="fas fa-info-circle"></i> "Finalizar Proceso" removerá a los candidatos de la pantalla de votación para comenzar un nuevo ciclo.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Finalizar -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" role="dialog" aria-labelledby="modalFinalizarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalFinalizarLabel">Finalizar y Archivar Proceso</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="index.php?action=votaciones_finalizar" method="POST">
                <?php echo Csrf::tokenField(); ?>
                <div class="modal-body">
                    <p>¿Está seguro de que desea finalizar el proceso de votación actual? Al hacerlo, los resultados se archivarán en el histórico.</p>
                    
                    <?php if (isset($stats) && isset($minimo_votantes) && $stats['votantes_unicos'] < $minimo_votantes): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i><strong>¡Quórum no alcanzado!</strong>
                        <br>Este proceso requiere un mínimo de <strong><?php echo $minimo_votantes; ?> votantes</strong>, pero solo han participado <strong><?php echo $stats['votantes_unicos']; ?></strong>. 
                        Si lo finaliza ahora, <strong>ningún postulado será aceptado</strong>. Si desea alargar el proceso, cancele esta acción y cambie la Fecha de Fin en la configuración.
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nombre_periodo"><strong>Nombre del Período para el Histórico:</strong></label>
                        <input type="text" class="form-control" id="nombre_periodo" name="nombre_periodo" 
                               placeholder="Ej: Elecciones Junta Directiva 2026-I" required
                               value="Votación <?php echo date('d-m-Y'); ?>">
                    </div>

                    <strong>Efectos:</strong>
                    <ul>
                        <li>Los resultados actuales se guardarán permanentemente en el Histórico.</li>
                        <li>Los candidatos dejarán de aparecer en las listas de votación activas.</li>
                        <li>Deberá configurar un nuevo período para la siguiente elección.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar y Archivar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
