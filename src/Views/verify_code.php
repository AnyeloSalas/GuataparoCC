<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - <?php echo env('APP_NAME', 'Sistema Guataparo'); ?></title>
    
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

        .verify-card {
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
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.85rem;
            text-align: center;
        }

        .code-input {
            letter-spacing: 12px;
            font-size: 2rem;
            text-align: center;
            font-weight: 800;
            border: 2px solid var(--primary-color);
            border-radius: 12px;
            color: var(--primary-color);
            background-color: #f8f9fc;
            padding: 10px;
            width: 100%;
            transition: all 0.3s;
        }

        .code-input:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 26, 114, 0.1);
            background-color: #ffffff;
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

        .back-link {
            display: block;
            margin-top: 24px;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
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
            .verify-card {
                padding: 24px;
            }
            .title {
                font-size: 1.3rem;
            }
            .logo-container img {
                max-width: 140px;
            }
            .code-input {
                font-size: 1.5rem;
                letter-spacing: 8px;
            }
        }
    </style>
</head>
<body>

    <div class="verify-card">
        <div class="logo-container">
            <img src="assets/img/logo_guataparo2.png" alt="Guataparo Country Club">
        </div>

        <h1 class="title">Verificar Código</h1>
        <p class="subtitle">Se ha enviado un código de 6 dígitos a su correo electrónico.</p>

        <?php if ($msg = flash('error')): ?>
            <div class="alert" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=verify_reset_code" method="POST">
            <?php echo Csrf::tokenField(); ?>
            
            <div class="form-group">
                <label for="code" class="form-label">Ingrese el código de verificación</label>
                <input type="text" name="code" id="code" class="code-input" 
                       maxlength="6" placeholder="000000" pattern="\d{6}" 
                       required autofocus autocomplete="off">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check-circle"></i> Verificar Código
            </button>
        </form>

        <a href="index.php?action=forgot_password" class="back-link">
            <i class="fas fa-arrow-left me-1"></i> Intentar con otro correo
        </a>
    </div>

</body>
</html>
