<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-building"></i> Sistema Guataparo
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <?php 
                $tipo = $_SESSION['tipousuario'] ?? '';
                if (strtoupper($tipo) !== 'SOCIO'): 
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=socios">
                        <i class="bi bi-people"></i> Socios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=conyugues">
                        <i class="bi bi-people"></i> Familiares
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=usuarios">
                        <i class="bi bi-person-badge"></i> Usuarios
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=votaciones">
                        <i class="bi bi-check2-square"></i> Votaciones
                    </a>
                </li>
                <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=reportes">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nombre_usuario'] ?? $_SESSION['usuario'] ?? 'Usuario'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (strtoupper($tipo) !== 'SOCIO'): ?>
                        <li><a class="dropdown-item" href="index.php?action=usuarios_change_password">Cambiar Contraseña</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="index.php?action=logout">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
