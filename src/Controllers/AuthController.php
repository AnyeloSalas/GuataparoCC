<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Socio.php';
require_once __DIR__ . '/../Models/AuditoriaHuella.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Controlador de Autenticación (AuthController)
 * 
 * Gestiona el inicio y cierre de sesión, la autenticación biométrica (huellas),
 * y el flujo de recuperación de contraseñas.
 */
class AuthController
{
    /** @var PDO|null Conexión a la base de datos */
    private $db;

    /** @var User Instancia del modelo User */
    private $user;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos y el modelo de usuario.
     */
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Muestra el formulario de inicio de sesión o procesa la solicitud de login.
     * Valida credenciales, maneja tokens CSRF y establece las variables de sesión.
     * 
     * @return void
     */
    public function login()
    {
        // Prevenir almacenamiento en caché del formulario de login
        // Esto ayuda a evitar "Token de seguridad inválido" por tokens expirados en caché
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                require_once PROJECT_ROOT . '/src/Views/login.php';
                return;
            }

            // Obtener y limpiar datos
            $username = Validator::clean(post('usuario'));
            $password = post('password');

            // Validar campos requeridos
            if (empty($username) || empty($password)) {
                flash('error', 'Por favor ingrese usuario y contraseña.');
                require_once PROJECT_ROOT . '/src/Views/login.php';
                return;
            }

            // Intentar login
            $loginResult = $this->user->login($username, $password);

            if ($loginResult === true) {
                // Iniciar sesión si no está iniciada
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                // Establecer variables de sesión
                $_SESSION['usuario'] = $this->user->usuario;
                $_SESSION['tipousuario'] = $this->user->tipousuario;
                $_SESSION['nombre_usuario'] = $this->user->usuario;
                $_SESSION['last_activity'] = time();

                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);

                flash('success', 'Bienvenido al sistema.');
                redirect('dashboard.php');
            } elseif ($loginResult === 'blocked') {
                flash('error', 'Su usuario ha sido bloqueado. Contacte al administrador.');
                require_once PROJECT_ROOT . '/src/Views/login.php';
            } else {
                flash('error', 'Usuario o contraseña incorrectos.');
                require_once PROJECT_ROOT . '/src/Views/login.php';
            }
        } else {
            // Si el usuario ya está autenticado y accede al login (GET), cerramos sesión.
            // Esto cumple con el requerimiento de cerrar sesión al interactuar con el botón "atrás" del navegador desde el dashboard.
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['usuario'])) {
                $this->logout();
                return;
            }

            // Get fingerprint configuration
            require_once __DIR__ . '/../Models/Configuracion.php';
            $configModel = new Configuracion($this->db);
            $huellaConfig = [
                'driver' => $configModel->get('huella_driver') ?: 'futronic',
                'hik_ip' => $configModel->get('huella_hik_ip') ?: '127.0.0.1',
                'hik_port' => $configModel->get('huella_hik_port') ?: '8000',
                'hik_user' => $configModel->get('huella_hik_user') ?: 'admin',
                'hik_pass' => $configModel->get('huella_hik_pass') ?: '12345',
                // URL del bridge local del lector — configurable desde .env → BRIDGE_LOCAL_URL
                'bridge_url' => $configModel->get('bridge_local_url') ?: env('BRIDGE_LOCAL_URL') ?: 'http://localhost:8888',
            ];

            // Mostrar formulario de login
            require_once PROJECT_ROOT . '/src/Views/login.php';
        }
    }

    /**
     * Cierra la sesión del usuario actual, destruyendo la cookie y los datos de sesión.
     * 
     * @return void
     */
    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Limpiar todas las variables de sesión
        $_SESSION = array();

        // Destruir la cookie de sesión si existe
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destruir la sesión
        session_destroy();

        flash('success', 'Sesión cerrada exitosamente.');
        redirect('index.php');
    }

    /**
     * NUEVO FLUJO (servidor en Linux sin FPModule SDK):
     * El JS captura la huella localmente vía bridge, luego pide los templates
     * almacenados al servidor. El JS compara localmente y envía matched_idx + score.
     * El servidor valida y crea la sesión.
     *
     * FLUJO LEGACY (servidor con bridge local): Si no viene matched_idx,
     * intenta la comparación server-side via bridge HTTP (backward compat).
     */
    public function loginHuella()
    {
        if (ob_get_level())
            ob_clean();
        header('Content-Type: application/json');

        if (session_status() == PHP_SESSION_NONE)
            session_start();

        try {
            if (!isPost()) {
                echo json_encode(['success' => false, 'message' => 'Método no permitido. Use POST.']);
                exit;
            }

            $capturedTemplate = post('template', '');
            $matchedIdx = post('matched_idx', null); // Índice del candidato ganador (nuevo flujo)
            $score = (int) post('score', 0);    // Score reportado por el bridge local

            // ── NUEVO FLUJO: JS reporta candidato, pero el SERVIDOR verifica independientemente ──
            if ($matchedIdx !== null) {

                if (empty($capturedTemplate) || strlen($capturedTemplate) < 100) {
                    echo json_encode(['success' => false, 'message' => 'Huella capturada inválida o corrupta.']);
                    exit;
                }

                // Recuperar la tabla de candidatos guardada en sesión por getFingerprintTemplates()
                if (empty($_SESSION['fp_candidates'])) {
                    echo json_encode(['success' => false, 'message' => 'Sesión de candidatos expirada. Intente nuevamente.']);
                    exit;
                }

                $candidates = $_SESSION['fp_candidates'];
                $idx = (int) $matchedIdx;

                if (!isset($candidates[$idx])) {
                    echo json_encode(['success' => false, 'message' => 'Candidato inválido.']);
                    exit;
                }

                $bestMatch = $candidates[$idx];
                unset($_SESSION['fp_candidates']); // Limpiar para uso único / anti-replay

                // VALIDACIÓN CRÍTICA EN SERVIDOR: Comparar capturedTemplate contra template desencriptado
                require_once __DIR__ . '/../Models/CryptoHelper.php';
                $storedTemplate = CryptoHelper::decrypt($bestMatch['template_to_check']);

                require_once __DIR__ . '/../Models/Configuracion.php';
                $configModel = new Configuracion($this->db);
                $driver = $configModel->get('huella_driver') ?: 'futronic';

                $serverScore = $this->verifyBiometric($capturedTemplate, $storedTemplate, $driver);
                $threshold = 50;

                if ($serverScore < $threshold) {
                    $auditoria = new AuditoriaHuella($this->db);
                    $auditoria->registrar(
                        'login_fallido',
                        $bestMatch['usuario'],
                        $bestMatch['cedula'] ?? null,
                        $serverScore,
                        "Intento de bypass o coincidencia no confirmada por el servidor (Score cliente: $score, Score servidor: $serverScore)"
                    );
                    echo json_encode(['success' => false, 'message' => "Verificación biométrica rechazada por el servidor (Score validado: $serverScore, mínimo: $threshold)"]);
                    exit;
                }

                // Validar estado del usuario/socio
                if ($bestMatch['origen'] === 'USUARIO' && !empty($bestMatch['bloqueado'])) {
                    echo json_encode(['success' => false, 'message' => 'Su usuario ha sido bloqueado. Contacte al administrador.']);
                    exit;
                }
                if ($bestMatch['origen'] === 'SOCIO' && isset($bestMatch['acciones_activas']) && $bestMatch['acciones_activas'] == 0) {
                    echo json_encode(['success' => false, 'message' => 'Socio Inactivo.']);
                    exit;
                }

                // Crear sesión
                $_SESSION['usuario'] = $bestMatch['usuario'];
                $_SESSION['tipousuario'] = $bestMatch['tipousuario'];
                $_SESSION['nombre_usuario'] = $bestMatch['nombre_real'] ?? $bestMatch['usuario'];
                $_SESSION['last_activity'] = time();
                $_SESSION['login_method'] = 'fingerprint_local';
                session_regenerate_id(true);

                $auditoria = new AuditoriaHuella($this->db);
                $auditoria->registrar(
                    'login_exitoso',
                    $bestMatch['usuario'],
                    $bestMatch['cedula'] ?? null,
                    $serverScore,
                    "Login biométrico exitoso verificado por servidor (Score: $serverScore)"
                );

                echo json_encode([
                    'success' => true,
                    'usuario' => $bestMatch['nombre_real'] ?? $bestMatch['usuario'],
                    'similarity' => $serverScore,
                    'message' => 'Login exitoso'
                ]);
                exit;
            }

            // ── FLUJO LEGACY: servidor intenta comparar via bridge HTTP ──────────────
            if (empty($capturedTemplate)) {
                echo json_encode(['success' => false, 'message' => 'Template vacío']);
                exit;
            }

            if (strlen($capturedTemplate) < 100) {
                echo json_encode(['success' => false, 'message' => 'Huella capturada inválida (template muy corto: ' . strlen($capturedTemplate) . ' bytes).']);
                exit;
            }

            // Obtener usuarios y socios con huella
            $queryUsers = "SELECT u.usuario, NULL as cedula, tu.nombre as tipousuario, u.huella_template,
                                  u.usuario as nombre_real, 'USUARIO' as origen, u.bloqueado
                           FROM usuarios u
                           LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                           WHERE u.huella_template IS NOT NULL AND u.huella_template != ''
                           AND CHAR_LENGTH(u.huella_template) >= 100";
            $stmtUsers = $this->db->prepare($queryUsers);
            $stmtUsers->execute();
            $usersWithFingerprint = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            $querySocios = "SELECT DISTINCT p.cedula as usuario, p.cedula, 'SOCIO' as tipousuario,
                                   p.huella_template,
                                   CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) as nombre_real,
                                   'SOCIO' as origen,
                                   (SELECT COUNT(*) FROM acciones a2 WHERE a2.persona_id = p.id AND a2.estado = 'ACTIVO') as acciones_activas
                            FROM personas p
                            WHERE p.huella_template IS NOT NULL AND p.huella_template != ''
                            AND CHAR_LENGTH(p.huella_template) >= 100";
            try {
                $stmtSocios = $this->db->prepare($querySocios);
                $stmtSocios->execute();
                $sociosWithFingerprint = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $sociosWithFingerprint = [];
            }

            $allFingerprints = array_merge($usersWithFingerprint, $sociosWithFingerprint);

            if (empty($allFingerprints)) {
                echo json_encode(['success' => false, 'message' => 'No hay huellas válidas registradas en el sistema.']);
                exit;
            }

            require_once __DIR__ . '/../Models/Configuracion.php';
            $configModel = new Configuracion($this->db);
            $driver = $configModel->get('huella_driver') ?: 'futronic';

            require_once __DIR__ . '/../Models/CryptoHelper.php';
            $bestMatch = null;
            $bestScore = 0;
            foreach ($allFingerprints as $record) {
                $decryptedTemplate = CryptoHelper::decrypt($record['huella_template']);
                $s = $this->verifyBiometric($capturedTemplate, $decryptedTemplate, $driver);
                if ($s > $bestScore) {
                    $bestScore = $s;
                    $bestMatch = $record;
                }
            }

            $auditoria = new AuditoriaHuella($this->db);
            $threshold = 50;

            if ($bestMatch && $bestScore >= $threshold) {
                if ($bestMatch['origen'] === 'USUARIO' && !empty($bestMatch['bloqueado'])) {
                    echo json_encode(['success' => false, 'message' => 'Su usuario ha sido bloqueado.']);
                    exit;
                }
                if ($bestMatch['origen'] === 'SOCIO' && isset($bestMatch['acciones_activas']) && $bestMatch['acciones_activas'] == 0) {
                    echo json_encode(['success' => false, 'message' => 'Socio Inactivo.']);
                    exit;
                }

                $_SESSION['usuario'] = $bestMatch['usuario'];
                $_SESSION['tipousuario'] = $bestMatch['tipousuario'];
                $_SESSION['nombre_usuario'] = $bestMatch['nombre_real'] ?? $bestMatch['usuario'];
                $_SESSION['last_activity'] = time();
                $_SESSION['login_method'] = 'fingerprint';
                session_regenerate_id(true);

                $auditoria->registrar('login_exitoso', $bestMatch['usuario'], $bestMatch['cedula'] ?? null, $bestScore, "Login biométrico exitoso (Score: $bestScore)");
                echo json_encode(['success' => true, 'usuario' => $bestMatch['nombre_real'] ?? $bestMatch['usuario'], 'similarity' => $bestScore, 'message' => 'Login exitoso']);
                exit;
            }

            $similarA = $bestMatch ? ($bestMatch['nombre_real'] ?? 'Desconocido') : 'Nadie';
            $auditoria->registrar('login_fallido', null, null, $bestScore, "Huella no reconocida. Mejor coincidencia: $bestScore (Posible: $similarA)");
            echo json_encode(['success' => false, 'message' => "Huella no reconocida. Score: $bestScore (Mínimo: $threshold)"]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Devuelve todos los templates de huellas al JS (sin datos de usuario).
     * La asociación índice→usuario se guarda en sesión para validación posterior.
     * Endpoint público (no requiere autenticación).
     */
    public function getFingerprintTemplates()
    {
        if (ob_get_level())
            ob_clean();
        header('Content-Type: application/json');

        if (session_status() == PHP_SESSION_NONE)
            session_start();

        try {
            $queryUsers = "SELECT u.usuario, NULL as cedula, tu.nombre as tipousuario, u.huella_template, u.huella_template_2,
                                  u.usuario as nombre_real, 'USUARIO' as origen, u.bloqueado
                           FROM usuarios u
                           LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                           WHERE (u.huella_template IS NOT NULL AND u.huella_template != '') OR (u.huella_template_2 IS NOT NULL AND u.huella_template_2 != '')";
            $stmtUsers = $this->db->prepare($queryUsers);
            $stmtUsers->execute();
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            $querySocios = "SELECT DISTINCT p.cedula as usuario, p.cedula, 'SOCIO' as tipousuario,
                                   p.huella_template, p.huella_template_2,
                                   CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido) as nombre_real,
                                   'SOCIO' as origen,
                                   (SELECT COUNT(*) FROM acciones a2 WHERE a2.persona_id = p.id AND a2.estado = 'ACTIVO') as acciones_activas
                            FROM personas p
                            WHERE (p.huella_template IS NOT NULL AND p.huella_template != '') OR (p.huella_template_2 IS NOT NULL AND p.huella_template_2 != '')";
            try {
                $stmtSocios = $this->db->prepare($querySocios);
                $stmtSocios->execute();
                $socios = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $socios = [];
            }

            $all_raw = array_merge($users, $socios);

            $all = [];
            foreach ($all_raw as $item) {
                if (!empty($item['huella_template']) && strlen($item['huella_template']) >= 100) {
                    $item1 = $item;
                    $item1['template_to_check'] = $item['huella_template'];
                    $all[] = $item1;
                }
                if (!empty($item['huella_template_2']) && strlen($item['huella_template_2']) >= 100) {
                    $item2 = $item;
                    $item2['template_to_check'] = $item['huella_template_2'];
                    $all[] = $item2;
                }
            }

            // Guardar mapping completo en sesión (el JS solo recibe índices y templates)
            $_SESSION['fp_candidates'] = array_values($all);

            require_once __DIR__ . '/../Models/CryptoHelper.php';
            // Devolver solo los templates con su índice (sin datos identificativos)
            $templates = array_map(function ($item, $idx) {
                return ['idx' => $idx, 'template' => CryptoHelper::decrypt($item['template_to_check'])];
            }, $all, array_keys($all));

            echo json_encode([
                'status' => 'ok',
                'total' => count($templates),
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Verificación biométrica server-side (flujo legacy).
     * Llama al bridge HTTP cuya URL se configura en BRIDGE_LOCAL_URL (.env).
     * En el nuevo flujo la comparación la hace el JS localmente; este método
     * se mantiene solo como fallback/compatibilidad.
     */
    private function verifyBiometric($t1, $t2, $driver)
    {
        $score = 0;

        // 1. Intentar HTTP Bridge local
        $bridgeUrl = defined('BRIDGE_LOCAL_URL') ? BRIDGE_LOCAL_URL
            : (env('BRIDGE_LOCAL_URL') ?: 'http://localhost:8888');

        $url = rtrim($bridgeUrl, '/') . '/verify?driver=' . urlencode($driver);
        $data = json_encode(['template1' => $t1, 'template2' => $t2]);

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => $data,
                'timeout' => 3
            ]
        ];

        try {
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            if ($result !== false) {
                $json = json_decode($result, true);
                if (isset($json['score'])) {
                    $score = (int) $json['score'];
                } elseif (isset($json['match']) && $json['match']) {
                    $score = 100;
                }
            }
        } catch (\Exception $e) {
            // El bridge HTTP no está disponible
        }

        // 2. Fallback: Ejecución directa de Python si HTTP falló o bridge no está activo
        if ($score === 0 && !empty($t1) && !empty($t2)) {
            $pythonPath = "C:\\laragon\\bin\\python\\python-3.10\\python.exe";
            if (!file_exists($pythonPath)) {
                $pythonPath = "python";
            }
            $scriptPath = PROJECT_ROOT . DIRECTORY_SEPARATOR . "huella" . DIRECTORY_SEPARATOR . "bridge_python.py";
            if (file_exists($scriptPath)) {
                $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " match --template " . escapeshellarg($t1) . " --template2 " . escapeshellarg($t2);
                $pyOutput = @shell_exec($cmd);
                if ($pyOutput) {
                    $pyJson = json_decode($pyOutput, true);
                    if ($pyJson && !empty($pyJson['match'])) {
                        $score = isset($pyJson['score']) ? (int)$pyJson['score'] : 100;
                    }
                }
            }
        }

        return (int) $score;
    }
    /**
     * Muestra el formulario y procesa el registro rápido de un socio desde la web.
     * 
     * @return void
     */
    public function registerSocio()
    {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (\Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                require_once PROJECT_ROOT . '/src/Views/register_socio.php';
                return;
            }

            // Instanciar modelo de Socio
            $socio = new Socio($this->db);

            // Limpiar datos
            $data = [
                'accion' => Validator::clean(post('accion')),
                'cedula' => Validator::clean(post('cedula')),
                'nombre' => Validator::clean(post('nombre')),
            ];

            // Guardar datos para repoblar formulario
            setOld($data);

            // Definir reglas de validación
            $rules = [
                'accion' => ['required', 'max:20'],
                'cedula' => ['required', 'venezuelan_id'],
                'nombre' => ['required', 'min:3', 'max:100'],
            ];

            // Validar
            if (!Validator::validate($rules, $data)) {
                flash('error', 'Por favor corrija los errores en el formulario.');
                require_once PROJECT_ROOT . '/src/Views/register_socio.php';
                return;
            }

            // Asignar al modelo
            $socio->accion = $data['accion'];
            $socio->cedula = $data['cedula'];
            $socio->nombre = $data['nombre'];
            $socio->estado = 'ACTIVO';
            $socio->usuario_refe = 'WEB_REGISTRY';

            // Intentar crear
            if ($socio->create()) {
                clearOld();
                flash('success', 'Socio registrado exitosamente.');
                require_once PROJECT_ROOT . '/src/Views/register_socio.php';
            } else {
                flash('error', 'Error al registrar. Verifique si el número de acción o cédula ya existen.');
                require_once PROJECT_ROOT . '/src/Views/register_socio.php';
            }
        } else {
            clearOld();
            require_once PROJECT_ROOT . '/src/Views/register_socio.php';
        }
    }

    /**
     * Muestra el formulario para solicitar la recuperación de contraseña.
     * 
     * @return void
     */
    public function forgotPassword()
    {
        require_once PROJECT_ROOT . '/src/Views/forgot_password.php';
    }

    /**
     * Procesa la solicitud de recuperación de contraseña.
     * Genera un código de verificación y lo envía por correo al usuario.
     * 
     * @return void
     */
    public function resetPasswordRequest()
    {
        if (!isPost()) {
            redirect('index.php?action=forgot_password');
        }

        try {
            CsrfMiddleware::verify();
        } catch (\Exception $e) {
            flash('error', 'Token de seguridad inválido o expirado. Por favor intente nuevamente.');
            redirect('index.php?action=forgot_password');
        }

        $email = Validator::clean(post('email'));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Por favor ingrese un correo válido.');
            redirect('index.php?action=forgot_password');
        }

        if ($this->user->findByEmail($email)) {
            // Reiniciar intentos de verificación
            $_SESSION['reset_attempts'] = 0;

            // Generate a 6-digit code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store code (reusing token column)
            $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            $query = "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email, $code]);

            // Send email with code
            $this->sendResetEmail($email, $code);

            // Redirect to code verification form
            $_SESSION['reset_email'] = $email;
            require_once PROJECT_ROOT . '/src/Views/verify_code.php';
            return;
        }

        flash('success', 'Si el correo existe, recibirá un código de verificación.');
        redirect('index.php?action=forgot_password');
    }

    private function sendResetEmail($email, $code)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');

            // Fix encryption setting
            $encryption = env('MAIL_ENCRYPTION', 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = env('MAIL_PORT', 587);
            $mail->CharSet = 'UTF-8';

            // Timeout settings for robustness
            $mail->Timeout = 10;
            $mail->SMTPConnectTimeout = 10;

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Sistema Guataparo'));
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Código de Recuperación - ' . env('APP_NAME', 'Sistema Guataparo');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #2c3e50; text-align: center;'>Código de Verificación</h2>
                    <p>Hola,</p>
                    <p>Usted ha solicitado restablecer su contraseña en el <strong>" . env('APP_NAME', 'Sistema Guataparo') . "</strong>.</p>
                    <p>Use el siguiente código para validar su identidad:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='background-color: #f8f9fa; color: #1e3c72; padding: 15px 30px; border: 2px dashed #1e3c72; border-radius: 5px; font-weight: bold; font-size: 24px; letter-spacing: 5px;'>$code</span>
                    </div>
                    <p>Este código es válido por 1 hora.</p>
                    <p>Si usted no solicitó este cambio, puede ignorar este correo de forma segura.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #7f8c8d; text-align: center;'>Este es un mensaje automático, por favor no responda.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            $logFile = LOGS_PATH . '/mail_error.log';
            $errorMsg = date('[Y-m-d H:i:s] ') . "Error enviando correo a $email: {$mail->ErrorInfo} | Exception: {$e->getMessage()}\n";
            file_put_contents($logFile, $errorMsg, FILE_APPEND);
            return false;
        }
    }

    /**
     * Verifica que el código de recuperación ingresado por el usuario sea válido y no haya expirado.
     * 
     * @return void
     */
    public function verifyResetCode()
    {
        if (!isPost()) {
            redirect('index.php?action=forgot_password');
        }

        try {
            CsrfMiddleware::verify();
        } catch (\Exception $e) {
            flash('error', 'Token de seguridad inválido o expirado.');
            redirect('index.php?action=forgot_password');
        }

        $email = $_SESSION['reset_email'] ?? '';
        $code = trim(post('code', ''));

        if (empty($email) || empty($code)) {
            flash('error', 'Datos insuficientes para procesar la verificación.');
            redirect('index.php?action=forgot_password');
        }

        // Control de intentos anti-fuerza bruta (máximo 5 intentos)
        $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;

        if ($_SESSION['reset_attempts'] > 5) {
            $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_attempts']);
            flash('error', 'Ha superado el número máximo de intentos permitidos (5). Por seguridad, el código ha sido cancelado. Solicite uno nuevo.');
            redirect('index.php?action=forgot_password');
        }

        // Verify code
        $query = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email, $code]);

        if ($stmt->rowCount() > 0) {
            // Success: limpiar intentos y generar token criptográfico seguro de 64 caracteres
            unset($_SESSION['reset_attempts']);
            $secureToken = bin2hex(random_bytes(32));

            // Sustituir el código de 6 dígitos por el token seguro en base de datos
            $upd = $this->db->prepare("UPDATE password_resets SET token = ? WHERE email = ?");
            $upd->execute([$secureToken, $email]);

            $token = $secureToken;
            require_once PROJECT_ROOT . '/src/Views/reset_password.php';
        } else {
            $remaining = 5 - $_SESSION['reset_attempts'];
            flash('error', "El código es incorrecto o ha expirado. Intentos restantes: {$remaining}");
            require_once PROJECT_ROOT . '/src/Views/verify_code.php';
        }
    }

    public function resetPasswordForm()
    {
        // This method is now handled by verifyResetCode, but let's keep it for compatibility or redirect
        redirect('index.php?action=forgot_password');
    }

    /**
     * Procesa la actualización de la contraseña una vez validado el token/código.
     * 
     * @return void
     */
    public function resetPasswordUpdate()
    {
        if (!isPost()) {
            redirect('index.php');
        }

        try {
            CsrfMiddleware::verify();
        } catch (\Exception $e) {
            flash('error', 'Token de seguridad inválido o expirado.');
            redirect('index.php?action=forgot_password');
        }

        $token = trim(post('token', ''));
        $password = post('password');
        $confirm_password = post('confirm_password');

        if (empty($token) || strlen($token) < 32) {
            flash('error', 'Token de restablecimiento inválido o expirado.');
            redirect('index.php?action=forgot_password');
        }

        if (empty($password) || strlen($password) < 6) {
            flash('error', 'La contraseña debe tener al menos 6 caracteres.');
            require_once PROJECT_ROOT . '/src/Views/reset_password.php';
            return;
        }

        if ($password !== $confirm_password) {
            flash('error', 'Las contraseñas no coinciden.');
            require_once PROJECT_ROOT . '/src/Views/reset_password.php';
            return;
        }

        // Verify token again and get email
        $query = "SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$token]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $email = $row['email'];

            // Find user by email
            if ($this->user->findByEmail($email)) {
                // Update password (using bcrypt hashing inside updatePassword)
                if ($this->user->updatePassword($this->user->usuario, $password)) {
                    // Delete token
                    $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_attempts']);

                    flash('success', 'Contraseña actualizada exitosamente. Ya puede iniciar sesión con su nueva contraseña.');
                    redirect('index.php?action=login');
                }
            }
        }

        flash('error', 'Ocurrió un error al restablecer la contraseña. El enlace puede haber expirado.');
        redirect('index.php?action=forgot_password');
    }
}
?>