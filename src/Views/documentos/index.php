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
        <i class="fas fa-file-download text-primary mr-2"></i>
        Documentos y Requisitos
    </h1>
</div>

<!-- Info Card -->
<div class="card shadow mb-4 border-left-info">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="fas fa-info-circle fa-2x text-info"></i>
            </div>
            <div class="col">
                <p class="mb-0">
                    Aquí puede descargar todos los documentos y requisitos necesarios para el proceso de postulación al Club Guataparo.
                    Haga clic en el botón de descarga para obtener cada documento.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Documents List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-gradient-primary">
        <h6 class="m-0 font-weight-bold text-white">
            <i class="fas fa-folder-open mr-2"></i>
            Documentos Disponibles
        </h6>
    </div>
    <div class="card-body">
        <?php if(empty($documentos)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-folder-open fa-4x mb-3"></i>
                <p>No hay documentos disponibles en este momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Documento</th>
                            <th class="d-none d-md-table-cell" style="width: 120px;">Tamaño</th>
                            <th style="width: 120px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($documentos as $doc): ?>
                        <tr>
                            <td class="text-center">
                                <i class="fas <?php echo $doc['icono']; ?> fa-lg"></i>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($doc['nombre']); ?></strong>
                                <br class="d-md-none">
                                <small class="text-muted d-md-none">
                                    <?php echo DocumentoController::formatFileSize($doc['tamano']); ?>
                                </small>
                            </td>
                            <td class="d-none d-md-table-cell text-muted">
                                <?php echo DocumentoController::formatFileSize($doc['tamano']); ?>
                            </td>
                            <td>
                                <a href="index.php?action=documentos_download&file=<?php echo urlencode($doc['nombre']); ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="Descargar">
                                    <i class="fas fa-download mr-1"></i>
                                    <span class="d-none d-sm-inline">Descargar</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-muted">
                <small>
                    <i class="fas fa-info-circle mr-1"></i>
                    Total: <?php echo count($documentos); ?> documento(s) disponible(s)
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
