<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - <?php echo env('APP_NAME', 'Sistema Guataparo'); ?></title>
    
    <!-- Fonts and Icons -->
    <link href="assets/vendor/css/fontawesome.min.css" rel="stylesheet" type="text/css">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #001a72;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #001a72;
            --text-muted: #6c757d;
            --input-border: #e2e8f0;
        }

        body {
            background-color: var(--bg-color) !important;
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 20px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .reset-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            padding: 32px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 24px;
        }

        .logo-container img {
            max-width: 160px;
            height: auto;
        }

        .title {
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 32px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .input-group-custom {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            font-size: 1.1rem;
            z-index: 10;
        }

        .form-control-custom {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 26, 114, 0.1);
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background-color: #00135a;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-align: left;
            background-color: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .reset-card {
                padding: 24px;
            }
            .title {
                font-size: 1.3rem;
            }
            .logo-container img {
                max-width: 140px;
            }
        }
    </style>
</head>
<body>

    <div class="reset-card">
        <div class="logo-container">
            <img src="assets/img/logo_guataparo2.png" alt="Guataparo Country Club">
        </div>

        <h1 class="title">Nueva Contraseña</h1>
        <p class="subtitle">Cree una nueva contraseña segura para su cuenta.</p>

        <?php if ($msg = flash('error')): ?>
            <div class="alert" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=reset_password_update" method="POST">
            <?php echo Csrf::tokenField(); ?>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
            
            <div class="form-group">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <div class="input-group-custom">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control-custom" 
                           placeholder="Mínimo 6 caracteres" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                <div class="input-group-custom">
                    <i class="fas fa-check-double input-icon"></i>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control-custom" 
                           placeholder="Repita la contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Cambiar Contraseña
            </button>
        </form>
    </div>

</body>
</html>
