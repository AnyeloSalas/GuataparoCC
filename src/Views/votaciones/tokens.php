<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Generar Acceso para Voto Remoto</h1>
    <a href="index.php?action=votaciones" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Volver a Votaciones
    </a>
</div>

<?php 
$flash_type = 'info';
$flash_msg = null;
if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    if (isset($_SESSION['flash']['type']) && isset($_SESSION['flash']['msg'])) {
        $flash_type = $_SESSION['flash']['type'];
        $flash_msg = $_SESSION['flash']['msg'];
        unset($_SESSION['flash']['type']);
        unset($_SESSION['flash']['msg']);
        if (empty($_SESSION['flash'])) unset($_SESSION['flash']);
    } else {
        foreach ($_SESSION['flash'] as $k => $v) {
            $flash_type = ($k === 'error') ? 'danger' : $k;
            $flash_msg = $v;
            unset($_SESSION['flash'][$k]);
            if (!empty($flash_msg)) break;
        }
    }
}

if ($flash_msg): 
?>
    <div class="alert alert-<?php echo htmlspecialchars($flash_type); ?> alert-dismissible fade show" role="alert">
        <?php echo $flash_msg; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-link"></i> Emitir Enlace de un Solo Uso</h6>
            </div>
            <div class="card-body">
                <p>Al generar un token de voto, el usuario podrá ingresar de manera remota **una única vez** a la boleta sin usar el validador biométrico. La votación se guardará bajo la acción que elija a continuación.</p>
                <form action="index.php?action=votaciones_remoto_generate" method="POST">
                    <?php echo Csrf::tokenField(); ?>
                    <div class="form-group mb-3">
                        <label for="accion"><strong>Seleccionar Acción / Socio:</strong></label>
                        <select name="accion" id="accion" class="form-control" required>
                            <option value="">Seleccione una Acción...</option>
                            <?php foreach ($acciones as $acc): ?>
                                <option value="<?php echo htmlspecialchars($acc['accion']); ?>">
                                    <?php echo htmlspecialchars($acc['display_text']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="horas"><strong>Validez del Enlace (Horas):</strong></label>
                        <input type="number" class="form-control" id="horas" name="horas" value="24" min="1" max="72" required>
                        <small class="text-muted">El enlace expirará por seguridad luego de este tiempo.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-qrcode mr-1"></i> Generar Enlace Remoto Seguro
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
