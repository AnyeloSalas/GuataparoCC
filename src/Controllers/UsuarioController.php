<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/PuestoTrabajo.php';
require_once __DIR__ . '/../Models/TipoUsuario.php';

class UsuarioController extends AdminController {
    private $usuario;

    public function __construct() {
        parent::__construct(); // Checks auth, valid session, CSRF on POST, and DB connection
        $this->verifyRole('Administrador');
        $this->usuario = new Usuario($this->db);
    }

    public function index() {
        // Auth check already done in constructor
        
        // Paginación
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $por_pagina = 10;
        if ($pagina < 1) $pagina = 1;
        
        $total_registros = $this->usuario->countAll();
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        // Obtener usuarios con información de huella
        $query = "SELECT u.usuario, u.email, tu.nombre as tipousuario, pt.nombre as puestotrabajo, u.bloqueado, 
                  CASE WHEN u.huella_template IS NOT NULL AND u.huella_template != '' THEN 1 ELSE 0 END as tiene_huella
                  FROM usuarios u 
                  LEFT JOIN tipos_usuario tu ON u.tipo_usuario_id = tu.id
                  LEFT JOIN puestos_trabajo pt ON u.puesto_trabajo_id = pt.id
                  ORDER BY u.usuario ASC LIMIT :offset, :limit";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit = $por_pagina, PDO::PARAM_INT);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get fingerprint configuration
            require_once __DIR__ . '/../Models/Configuracion.php';
            $configModel = new Configuracion($this->db);
            $huellaConfig = [
                'driver' => $configModel->get('huella_driver') ?: 'futronic',
                'hik_ip' => $configModel->get('huella_hik_ip') ?: '127.0.0.1',
                'hik_port' => $configModel->get('huella_hik_port') ?: '8000',
                'hik_user' => $configModel->get('huella_hik_user') ?: 'admin',
                'hik_pass' => $configModel->get('huella_hik_pass') ?: '12345'
            ];
            
            // Define PROJECT_ROOT if not defined (fallback)
            if (!defined('PROJECT_ROOT')) {
                define('PROJECT_ROOT', dirname(dirname(__DIR__)));
            }
            
            require PROJECT_ROOT . '/src/Views/usuarios/index.php';
        } catch (Exception $e) {
            // Log error
            die("Error al cargar usuarios.");
        }
    }

    public function create() {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=usuarios');
                return;
            }

            // Limpiar datos
            $data = [
                'usuario' => Validator::clean(post('usuario')),
                'email' => Validator::clean(post('email')),
                'contrasena' => post('contrasena'),
                'tipo_usuario_id' => Validator::clean(post('tipo_usuario_id')),
                'puesto_trabajo_id' => Validator::clean(post('puesto_trabajo_id')),
                'bloqueado' => isset($_POST['bloqueado']) ? 1 : 0,
            ];

            // Guardar datos para repoblar formulario en caso de error
            setOld($data);

            // Definir reglas de validación
            $rules = [
                'usuario' => ['required', 'min:3', 'max:50'],
                'email' => ['email', 'max:255'],
                'contrasena' => ['required', 'min:6'],
                'tipo_usuario_id' => ['required'],
            ];

            // Validar
            if (!Validator::validate($rules, $data)) {
                $errors = Validator::getErrors();
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/usuarios/create.php';
                return;
            }

            // Verificar si el usuario ya existe
            if ($this->usuario->existsByUsername($data['usuario'])) {
                flash('error', "El nombre de usuario '" . htmlspecialchars($data['usuario']) . "' ya existe.");
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/usuarios/create.php';
                return;
            }

            // Asignar al modelo
            $this->usuario->usuario = $data['usuario'];
            $this->usuario->email = $data['email'];
            $this->usuario->contrasena = $data['contrasena'];
            $this->usuario->tipo_usuario_id = $data['tipo_usuario_id'];
            $this->usuario->puesto_trabajo_id = $data['puesto_trabajo_id'];
            $this->usuario->bloqueado = $data['bloqueado'];

            // Intentar crear
            if ($this->usuario->create()) {
                clearOld();
                flash('success', 'Usuario creado exitosamente.');
                redirect('index.php?action=usuarios');
            } else {
                flash('error', 'Error al crear el usuario. Es posible que el nombre de usuario ya existe.');
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/usuarios/create.php';
            }
        } else {
            clearOld();
            $puestoModel = new PuestoTrabajo($this->db);
            $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
            $tipoUsuarioModel = new TipoUsuario($this->db);
            $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
            require PROJECT_ROOT . '/src/Views/usuarios/create.php';
        }
    }

    public function edit() {
        $usuario = get('usuario');
        if ($usuario) {
            $this->usuario->usuario = Validator::clean($usuario);
            if ($this->usuario->readOne()) {
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require_once __DIR__ . '/../Views/usuarios/edit.php';
            } else {
                flash('error', 'Usuario no encontrado.');
                redirect('index.php?action=usuarios');
            }
        } else {
            flash('error', 'Usuario no especificado.');
            redirect('index.php?action=usuarios');
        }
    }

    public function update() {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=usuarios');
                return;
            }

            // Limpiar datos
            $data = [
                'usuario' => Validator::clean(post('usuario')),
                'email' => Validator::clean(post('email')),
                'tipo_usuario_id' => Validator::clean(post('tipo_usuario_id')),
                'puesto_trabajo_id' => Validator::clean(post('puesto_trabajo_id')),
                'bloqueado' => isset($_POST['bloqueado']) ? 1 : 0,
            ];

            // Guardar datos para repoblar formulario
            setOld($data);

            // Definir reglas de validación
            $rules = [
                'usuario' => ['required', 'min:3', 'max:50'],
                'email' => ['email', 'max:255'],
                'tipo_usuario_id' => ['required'],
            ];

            // Validar
            if (!Validator::validate($rules, $data)) {
                flash('error', 'Por favor corrija los errores en el formulario.');
                $this->usuario->usuario = $data['usuario'];
                $this->usuario->readOne();
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/usuarios/edit.php';
                return;
            }

            // Asignar al modelo
            $this->usuario->usuario = $data['usuario'];
            $this->usuario->email = $data['email'];
            $this->usuario->tipo_usuario_id = $data['tipo_usuario_id'];
            $this->usuario->puesto_trabajo_id = $data['puesto_trabajo_id'];
            $this->usuario->bloqueado = $data['bloqueado'];

            // Intentar actualizar
            if ($this->usuario->update()) {
                clearOld();
                flash('success', 'Usuario actualizado exitosamente.');
                redirect('index.php?action=usuarios');
            } else {
                flash('error', 'Error al actualizar usuario.');
                $this->usuario->readOne();
                $puestoModel = new PuestoTrabajo($this->db);
                $puestos_trabajo = $puestoModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                $tipoUsuarioModel = new TipoUsuario($this->db);
                $tipos_usuario = $tipoUsuarioModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
                require PROJECT_ROOT . '/src/Views/usuarios/edit.php';
            }
        } else {
            redirect('index.php?action=usuarios');
        }
    }

    public function changePassword() {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=usuarios');
                return;
            }

            $this->usuario->usuario = Validator::clean(post('usuario'));
            $current_password = post('current_password');
            $new_password = post('new_password');
            $confirm_password = post('confirm_password');

            // Verificar contraseña actual
            if ($this->usuario->readOne()) {
                if (!$this->usuario->verifyPassword($current_password)) {
                    flash('error', 'La contraseña actual es incorrecta.');
                    require_once __DIR__ . '/../Views/usuarios/change_password.php';
                    return;
                }

                if ($new_password !== $confirm_password) {
                    flash('error', 'Las contraseñas nuevas no coinciden.');
                    require_once __DIR__ . '/../Views/usuarios/change_password.php';
                    return;
                }

                if (!Validator::validatePasswordStrength($new_password)) {
                    flash('error', 'La contraseña debe tener al menos 6 caracteres.');
                    require_once __DIR__ . '/../Views/usuarios/change_password.php';
                    return;
                }

                $this->usuario->contrasena = $new_password;
                if ($this->usuario->updatePassword()) {
                    flash('success', 'Contraseña actualizada exitosamente.');
                    redirect('index.php?action=usuarios');
                } else {
                    flash('error', 'Error al actualizar la contraseña.');
                    require_once __DIR__ . '/../Views/usuarios/change_password.php';
                }
            }
        } else {
            $usuario = get('usuario');
            if ($usuario) {
                $this->usuario->usuario = Validator::clean($usuario);
                require PROJECT_ROOT . '/src/Views/usuarios/change_password.php';
            } else {
                flash('error', 'Usuario no especificado.');
                redirect('index.php?action=usuarios');
            }
        }
    }

    public function delete() {
        if (!isPost()) {
            flash('error', 'Método no permitido.');
            redirect('index.php?action=usuarios');
            return;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
            redirect('index.php?action=usuarios');
            return;
        }

        $usuario = post('usuario');
        if ($usuario) {
            $usuarioToDelete = Validator::clean($usuario);
            
            // Prevenir auto-eliminación
            if ($usuarioToDelete === $_SESSION['usuario']) {
                flash('error', 'No puede eliminar su propio usuario.');
                redirect('index.php?action=usuarios');
                return;
            }

            $this->usuario->usuario = $usuarioToDelete;
            if ($this->usuario->delete()) {
                flash('success', 'Usuario eliminado exitosamente.');
                redirect('index.php?action=usuarios');
            } else {
                flash('error', 'Error al eliminar usuario.');
                redirect('index.php?action=usuarios');
            }
        } else {
            flash('error', 'Usuario no especificado.');
            redirect('index.php?action=usuarios');
        }
    }

    public function toggleBlock() {
        if (!isPost()) {
            flash('error', 'Método no permitido.');
            redirect('index.php?action=usuarios');
            return;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
            redirect('index.php?action=usuarios');
            return;
        }

        $usuario = post('usuario');
        $status = post('status'); // 1 to block, 0 to unblock
        
        if ($usuario && isset($status)) {
            $userToToggle = Validator::clean($usuario);
            $newStatus = (int)$status;
            
            // Prevent blocking self
            if ($userToToggle === $_SESSION['usuario']) {
                flash('error', 'No puede bloquear su propio usuario.');
                redirect('index.php?action=usuarios');
                return;
            }

            $this->usuario->usuario = $userToToggle;
            if ($this->usuario->toggleBlock($newStatus)) {
                $msg = $newStatus == 1 ? 'Usuario bloqueado exitosamente.' : 'Usuario desbloqueado exitosamente.';
                flash('success', $msg);
            } else {
                flash('error', 'Error al cambiar estado del usuario.');
            }
        } else {
            flash('error', 'Datos incompletos.');
        }
        redirect('index.php?action=usuarios');
    }
}
?>
