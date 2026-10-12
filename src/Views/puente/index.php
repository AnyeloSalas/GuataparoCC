<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fingerprint text-primary mr-2"></i>Gestión del Puente Biométrico</h1>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle mr-2"></i> <?php echo htmlspecialchars($_SESSION['info']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Status Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4 border-left-<?php echo $status ? 'success' : 'danger'; ?>">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-<?php echo $status ? 'success' : 'danger'; ?>">Estado del Servicio</h6>
                </div>
                <div class="card-body text-center py-5">
                    <?php if ($status): ?>
                        <div class="mb-4">
                            <div class="spinner-grow text-success" role="status" style="width: 3rem; height: 3rem;">
                                <span class="sr-only">En ejecución...</span>
                            </div>
                        </div>
                        <h4 class="text-success font-weight-bold mb-1">EN EJECUCIÓN</h4>
                        <p class="text-muted mb-4">Puerto: 8888</p>
                        
                        <form action="index.php?action=puente_stop" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea detener el puente? Esto afectará a los usuarios que estén intentando validar su huella en este momento.');">
                            <?php echo Csrf::tokenField(); ?>
                            <button type="submit" class="btn btn-danger btn-icon-split btn-lg shadow-sm border-0">
                                <span class="icon text-white-50">
                                    <i class="fas fa-stop"></i>
                                </span>
                                <span class="text">Detener Puente</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fas fa-power-off text-danger" style="font-size: 3rem; opacity: 0.8;"></i>
                        </div>
                        <h4 class="text-danger font-weight-bold mb-1">DETENIDO</h4>
                        <p class="text-muted mb-4">El puente no está respondiendo.</p>
                        
                        <form action="index.php?action=puente_start" method="POST" class="d-inline">
                            <?php echo Csrf::tokenField(); ?>
                            <button type="submit" class="btn btn-success btn-icon-split btn-lg shadow-sm border-0">
                                <span class="icon text-white-50">
                                    <i class="fas fa-play"></i>
                                </span>
                                <span class="text">Iniciar Puente</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <hr class="mt-5 mb-4">
                    <p class="small text-muted text-left mb-0">
                        <i class="fas fa-info-circle mr-1"></i> El puente actúa como intermediario entre la aplicación web y el lector de huellas USB (Hikvision). Debe estar en ejecución para enrolar y verificar huellas.
                    </p>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Atajo (Windows)</h6>
                </div>
                <div class="card-body">
                    <p class="small">Alternativamente, puede iniciar el puente ejecutando el archivo por lotes ubicado en la carpeta del sistema:</p>
                    <code class="d-block p-2 bg-light text-dark rounded border mb-2">C:\laragon\www\php_system\huella\iniciar_puente.bat</code>
                    <p class="small text-muted mb-0">Hacer doble clic en este archivo abrirá una ventana de consola donde podrá ver la ejecución en tiempo real.</p>
                </div>
            </div>
        </div>

        <!-- Logs Card -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Registros (Logs)</h6>
                    <form action="index.php?action=puente_clear_logs" method="POST" class="m-0" onsubmit="return confirm('¿Limpiar todos los logs?');">
                        <?php echo Csrf::tokenField(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash fa-sm"></i> Limpiar
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="bg-dark text-light p-3 rounded" style="height: 500px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; word-break: break-all;">
                        <?php if (empty($logs)): ?>
                            <div class="text-center text-muted mt-5">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>No hay registros disponibles o el archivo está vacío.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <?php 
                                    // Syntax highlighting para logs
                                    $logHtml = htmlspecialchars($log);
                                    if (strpos($logHtml, 'error') !== false || strpos($logHtml, 'fail') !== false) {
                                        $logHtml = '<span style="color: #ff6b6b;">' . $logHtml . '</span>';
                                    } elseif (strpos($logHtml, 'success') !== false) {
                                        $logHtml = '<span style="color: #51cf66;">' . $logHtml . '</span>';
                                    } elseif (strpos($logHtml, 'time') !== false) {
                                        $logHtml = '<span style="color: #339af0;">' . $logHtml . '</span>';
                                    }

                                ?>
                                <div class="mb-1 border-bottom border-secondary pb-1" style="border-color: #444 !important;"><?php echo $logHtml; ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
