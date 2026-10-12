<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-brain text-warning mr-2"></i>Entrenamiento de IA
        </h1>
        <a href="index.php?action=ia_dashboard" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Chat
        </a>
    </div>

    <?php if(isset($success) || isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> Operación realizada exitosamente.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulario de Entrenamiento -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-warning text-white">
                    <h6 class="m-0 font-weight-bold">Agregar Nuevo Conocimiento</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?action=ia_entrenamiento">
                        <?php echo Csrf::tokenField(); ?>
                        <div class="form-group">
                            <label for="palabras_clave" class="font-weight-bold">Palabras Clave / Pregunta</label>
                            <input type="text" class="form-control" id="palabras_clave" name="palabras_clave" 
                                   placeholder="Ej: horario piscina, a que hora abren" required>
                            <small class="form-text text-muted">Frases que activarán esta respuesta.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria" class="font-weight-bold">Categoría</label>
                            <select class="form-control" id="categoria" name="categoria">
                                <option value="General">General</option>
                                <option value="Horarios">Horarios</option>
                                <option value="Normas">Normas</option>
                                <option value="Eventos">Eventos</option>
                                <option value="Historia">Historia</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="respuesta" class="font-weight-bold">Respuesta de la IA</label>
                            <textarea class="form-control" id="respuesta" name="respuesta" rows="5" 
                                      placeholder="Escribe exactamente lo que quieres que responda la IA..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block font-weight-bold text-dark">
                            <i class="fas fa-save mr-2"></i> Guardar Conocimiento
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Conocimientos -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Base de Conocimiento Actual</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Palabras Clave</th>
                                    <th>Respuesta</th>
                                    <th>Categoría</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($conocimientos)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-brain fa-3x mb-3 text-gray-300"></i>
                                            <p>La base de conocimiento está vacía. ¡Agrega el primer dato!</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($conocimientos as $c): ?>
                                    <tr>
                                        <td><span class="badge badge-light border"><?php echo htmlspecialchars($c['palabras_clave']); ?></span></td>
                                        <td><?php echo nl2br(htmlspecialchars(substr($c['respuesta'], 0, 100))) . (strlen($c['respuesta']) > 100 ? '...' : ''); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($c['categoria']); ?></span></td>
                                        <td class="text-center">
                                            <form action="index.php?action=ia_entrenamiento" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este conocimiento?');">
                                                <?php echo Csrf::tokenField(); ?>
                                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($c['id']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm btn-circle" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
