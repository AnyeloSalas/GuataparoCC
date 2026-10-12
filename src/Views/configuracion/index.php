<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Ensure Database connection and Configuration model are loaded
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Models/Configuracion.php';

try {
    $db_conn = (new Database())->getConnection();
    $configModel = new Configuracion($db_conn);
    
    // Auto-seed color_letra if missing
    if ($configModel->get('color_letra') === null) {
        $configModel->create('color_letra', '#858796', 'Color de texto principal', 'color', 'apariencia');
        echo "<script>window.location.reload();</script>";
        exit;
    }
} catch (Exception $e) {
    // Continue if DB error
}

// Category names in Spanish
$categoriasNombres = [
    'general' => 'Información General',
    'apariencia' => 'Apariencia',
    'seguridad' => 'Seguridad',
    'notificaciones' => 'Notificaciones',
    'votacion' => 'Votación',
    'lector_huellas' => 'Lector de Huellas'
];

// Category icons
$categoriasIconos = [
    'general' => 'fas fa-info-circle',
    'apariencia' => 'fas fa-palette',
    'seguridad' => 'fas fa-shield-alt',
    'notificaciones' => 'fas fa-bell',
    'votacion' => 'fas fa-check-square',
    'lector_huellas' => 'fas fa-fingerprint'
];

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Configuración del Sistema</h1>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['info'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i> <?php echo $_SESSION['info']; unset($_SESSION['info']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Configuration Form -->
<form action="index.php?action=configuracion_update" method="POST" id="configForm">
    <input type="hidden" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">
    
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
        <?php $isActive = true; ?>
        <?php foreach ($configPorCategoria as $categoria => $configs): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" 
                   id="tab-<?php echo $categoria; ?>" 
                   data-toggle="tab" 
                   href="#content-<?php echo $categoria; ?>" 
                   role="tab" 
                   aria-controls="content-<?php echo $categoria; ?>" 
                   aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>">
                    <i class="<?php echo $categoriasIconos[$categoria] ?? 'fas fa-cogs'; ?> mr-2"></i>
                    <?php echo $categoriasNombres[$categoria] ?? ucfirst($categoria); ?>
                </a>
            </li>
            <?php $isActive = false; ?>
        <?php endforeach; ?>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="configTabsContent">
        <?php $isActive = true; ?>
        <?php foreach ($configPorCategoria as $categoria => $configs): ?>
            <div class="tab-pane fade <?php echo $isActive ? 'show active' : ''; ?>" 
                 id="content-<?php echo $categoria; ?>" 
                 role="tabpanel" 
                 aria-labelledby="tab-<?php echo $categoria; ?>">
                
                <div class="card shadow mb-4 border-left-primary">
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($configs as $config): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="<?php echo $config['clave']; ?>" class="font-weight-bold text-gray-700">
                                        <?php echo htmlspecialchars($config['descripcion']); ?>
                                    </label>
                                    
                                    <?php if ($config['tipo'] == 'text' || $config['tipo'] == 'email'): ?>
                                        <input type="<?php echo $config['tipo']; ?>" 
                                                class="form-control" 
                                                id="<?php echo $config['clave']; ?>" 
                                                name="<?php echo $config['clave']; ?>" 
                                                value="<?php echo htmlspecialchars($config['valor']); ?>">
                                    
                                    <?php elseif ($config['tipo'] == 'number'): ?>
                                        <input type="number" 
                                                class="form-control" 
                                                id="<?php echo $config['clave']; ?>" 
                                                name="<?php echo $config['clave']; ?>" 
                                                value="<?php echo htmlspecialchars($config['valor']); ?>">
                                    
                                    <?php elseif ($config['tipo'] == 'color'): ?>
                                        <div class="input-group">
                                            <input type="color" 
                                                   class="form-control form-control-color" 
                                                   id="<?php echo $config['clave']; ?>" 
                                                   name="<?php echo $config['clave']; ?>" 
                                                   value="<?php echo htmlspecialchars($config['valor']); ?>" 
                                                   title="Elige un color">
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="<?php echo htmlspecialchars($config['valor']); ?>" 
                                                   readonly>
                                        </div>
                                    
                                    <?php elseif ($config['tipo'] == 'boolean'): ?>
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="<?php echo $config['clave']; ?>" value="0">
                                            <input type="checkbox" 
                                                   class="custom-control-input" 
                                                   id="<?php echo $config['clave']; ?>" 
                                                   name="<?php echo $config['clave']; ?>" 
                                                   value="1" 
                                                   <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="<?php echo $config['clave']; ?>">Activado</label>
                                        </div>
                                    
                                    <?php elseif ($config['tipo'] == 'textarea'): ?>
                                        <textarea class="form-control" 
                                                    id="<?php echo $config['clave']; ?>" 
                                                    name="<?php echo $config['clave']; ?>" 
                                                    rows="3"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted d-block mt-1">Clave interna: <code><?php echo $config['clave']; ?></code></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $isActive = false; ?>
        <?php endforeach; ?>
    </div>

    <!-- Submit Button -->
    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm">
            <i class="fas fa-save"></i> Guardar Todos los Cambios
        </button>
    </div>
</form>

<!-- Additional Actions -->
<div class="card shadow mb-4 border-left-warning">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-tools"></i> Acciones Avanzadas</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="font-weight-bold">Crear Nueva Configuración</h6>
                <p class="text-muted">Agrega un nuevo parámetro de configuración al sistema.</p>
                <a href="index.php?action=configuracion_create" class="btn btn-outline-primary">
                    <i class="fas fa-plus-circle"></i> Nueva Configuración
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="font-weight-bold">Restablecer Valores</h6>
                <p class="text-muted">Restaura todas las configuraciones a sus valores por defecto.</p>
                <button type="button" class="btn btn-outline-warning" onclick="confirmarReset()">
                    <i class="fas fa-undo"></i> Restablecer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarReset() {
        if (confirm('¿Estás seguro de que deseas restablecer todas las configuraciones a sus valores por defecto? Esta acción no se puede deshacer.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?action=configuracion_reset';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = document.querySelector('input[name="csrf_token"]').value;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
