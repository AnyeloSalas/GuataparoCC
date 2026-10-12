<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../Models/Socio.php';

/**
 * Controlador de Socios (SocioController)
 * 
 * Gestiona todas las operaciones relacionadas con los socios (acciones) del club.
 * Incluye listado, creación, edición, eliminación, cambio de estado y la
 * integración con el sistema de huellas dactilares y votaciones.
 */
class SocioController
{
    /** @var PDO|null Conexión a la base de datos */
    private $db;

    /** @var Socio Instancia del modelo Socio */
    private $socio;

    /**
     * Constructor del controlador.
     * Verifica la autenticación mediante middleware e inicializa la conexión a BD y el modelo.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar autenticación
        AuthMiddleware::require();

        $database = new Database();
        $this->db = $database->getConnection();
        $this->socio = new Socio($this->db);
    }

    /**
     * Muestra el listado principal de socios.
     * Maneja filtros de búsqueda, paginación y carga la configuración del lector de huellas.
     * 
     * @return void
     */
    public function index()
    {
        $filtros = [
            'nombre' => get('search_nombre'),
            'cedula' => get('search_cedula'),
            'accion' => get('search_accion'),
            'keyword' => get('search')
        ];

        $search = $filtros; // Pass the whole array to the model

        // Paginación
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $por_pagina = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($pagina < 1)
            $pagina = 1;

        $total_registros = $this->socio->countWithFingerprints($search);
        $total_paginas = ceil($total_registros / $por_pagina);
        $offset = ($pagina - 1) * $por_pagina;

        // Get socios with fingerprint information using the Model
        $stmt = $this->socio->readWithFingerprints($search, $offset, $por_pagina);
        $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        // Definir estados posibles
        $estados_posibles = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];

        require_once PROJECT_ROOT . '/src/Views/socios/index.php';
    }

    /**
     * Busca un postulado por su número de cédula y verifica si cumple con los requisitos
     * de votación (quórum y porcentaje de aceptación) para ser admitido como socio.
     * Devuelve una respuesta JSON.
     * 
     * @return void
     */
    public function buscarPostulado()
    {
        // Asegurar que no haya salida previa
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');

        $cedula = get('cedula');
        if (!$cedula) {
            echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
            return;
        }

        try {
            require_once PROJECT_ROOT . '/src/Models/Postulado.php';
            require_once PROJECT_ROOT . '/src/Models/Votacion.php';

            $postulado = new Postulado($this->db);
            $postulado->cedula = $cedula;

            if ($postulado->readOne()) {
                $votacion = new Votacion($this->db);
                require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
                $configModel = new Configuracion($this->db);
                $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
                $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

                $stats = $votacion->getEstadisticas();
                $quorum_alcanzado = ($stats['votantes_unicos'] >= $minimo_votantes);

                $resultado = $votacion->getResultadoPorCedula($cedula);

                // Asegurar que resultado sea un array
                if (!$resultado) {
                    $resultado = ['aceptados' => 0, 'rechazados' => 0, 'otros' => 0];
                }

                $aceptados = (int) ($resultado['aceptados'] ?? 0);
                $rechazados = (int) ($resultado['rechazados'] ?? 0);
                $total = $aceptados + $rechazados + (int) ($resultado['otros'] ?? 0);
                $req = ceil(($total * $porcentaje_req) / 100);

                if ($quorum_alcanzado && $aceptados >= $req && $total > 0) {
                    // Mapear profesión a ID si es posible
                    $prof_id = null;
                    if (!empty($postulado->profesion)) {
                        $p_stmt = $this->db->prepare("SELECT id FROM profesiones WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
                        $p_stmt->execute([$postulado->profesion]);
                        if ($p_row = $p_stmt->fetch())
                            $prof_id = $p_row['id'];
                    }

                    $response = [
                        'success' => true,
                        'data' => [
                            'cedula' => $postulado->cedula,
                            'primer_nombre' => $postulado->primer_nombre,
                            'segundo_nombre' => $postulado->segundo_nombre,
                            'primer_apellido' => $postulado->primer_apellido,
                            'segundo_apellido' => $postulado->segundo_apellido,
                            'profesion_id' => $prof_id,
                            'correo_electronico' => $postulado->correo_electronico,
                            'nacionalidad' => $postulado->nac_codigo, // Corregido: era nacionalidad
                            'email' => $postulado->correo_electronico,
                            'telefono_movil' => null,
                            'sexo' => null,
                            'fecha_nacimiento' => null,
                        ],
                        'votacion' => [
                            'aceptados' => $aceptados,
                            'rechazados' => $rechazados,
                            'status' => 'ACEPTADO'
                        ]
                    ];

                    // Buscar datos adicionales en tabla personas si existe
                    $solo_cedula = preg_replace('/[^0-9]/', '', $cedula);
                    $stmt_p = $this->db->prepare("SELECT email, telefono_movil, sexo, fecha_nacimiento, profesion_id, lugar_trabajo FROM personas WHERE cedula = ? LIMIT 1");
                    $stmt_p->execute([$solo_cedula]);
                    if ($p_row = $stmt_p->fetch(PDO::FETCH_ASSOC)) {
                        if ($p_row['email'])
                            $response['data']['email'] = $p_row['email'];
                        if ($p_row['telefono_movil'])
                            $response['data']['telefono_movil'] = $p_row['telefono_movil'];
                        if ($p_row['sexo'])
                            $response['data']['sexo'] = $p_row['sexo'];
                        if ($p_row['fecha_nacimiento'])
                            $response['data']['fecha_nacimiento'] = $p_row['fecha_nacimiento'];
                        if ($p_row['profesion_id'])
                            $response['data']['profesion_id'] = $p_row['profesion_id'];
                        if ($p_row['lugar_trabajo'])
                            $response['data']['lugar_trabajo'] = $p_row['lugar_trabajo'];
                    }

                    echo json_encode($response);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => "La persona tiene un resultado de votación desfavorable (Aceptados: $aceptados, Rechazados: $rechazados).",
                        'votacion' => [
                            'aceptados' => $aceptados,
                            'rechazados' => $rechazados,
                            'status' => 'RECHAZADO'
                        ]
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cédula no encontrada en el registro de postulados']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Procesa la creación de un nuevo socio a partir de un postulado admitido.
     * Valida los datos del formulario, verifica nuevamente los resultados de votación
     * y asegura que la cédula no esté duplicada antes de registrar la acción.
     * 
     * @return void
     */
    public function create()
    {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=socios');
                return;
            }

            // Limpiar datos
            $data = [
                'accion' => Validator::clean(post('accion')),
                'cedula' => Validator::clean(post('cedula')),
                'nacionalidad' => Validator::clean(post('nacionalidad', 'V')),
                'primer_nombre' => Validator::clean(post('primer_nombre')),
                'segundo_nombre' => Validator::clean(post('segundo_nombre')),
                'primer_apellido' => Validator::clean(post('primer_apellido')),
                'segundo_apellido' => Validator::clean(post('segundo_apellido')),
                'email' => Validator::clean(post('email')),
                'telefono_movil' => Validator::clean(post('telefono_movil')),
                'sexo' => Validator::clean(post('sexo')),
                'fecha_nacimiento' => Validator::clean(post('fecha_nacimiento')),
                'profesion_id' => Validator::clean(post('profesion_id')),
                'lugar_trabajo' => Validator::clean(post('lugar_trabajo')),
                'estado' => Validator::clean(post('estado', 'ACTIVO')),
            ];

            // Guardar datos para repoblar formulario
            setOld($data);

            // Definir reglas de validación
            $rules = [
                'accion' => ['required', 'max:20'],
                'cedula' => ['required', 'venezuelan_id'],
                'primer_nombre' => ['required', 'min:2', 'max:100'],
                'primer_apellido' => ['required', 'min:2', 'max:100'],
                'estado' => ['required'],
            ];

            // Validar
            if (!Validator::validate($rules, $data)) {
                flash('error', 'Por favor corrija los errores en el formulario.');
                $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
                require_once PROJECT_ROOT . '/src/Views/socios/create.php';
                return;
            }

            // --- VERIFICACIÓN DE VOTACIÓN ---
            require_once PROJECT_ROOT . '/src/Models/Votacion.php';
            require_once PROJECT_ROOT . '/src/Models/Configuracion.php';
            $votacionModel = new Votacion($this->db);
            $configModel = new Configuracion($this->db);
            $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
            $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

            $stats = $votacionModel->getEstadisticas();
            $quorum_alcanzado = ($stats['votantes_unicos'] >= $minimo_votantes);

            $resultado = $votacionModel->getResultadoPorCedula($data['cedula']);
            $aceptados = (int) ($resultado['aceptados'] ?? 0);
            $rechazados = (int) ($resultado['rechazados'] ?? 0);
            $total = $aceptados + $rechazados + (int) ($resultado['otros'] ?? 0);
            $req = ceil(($total * $porcentaje_req) / 100);

            if ($total === 0) {
                flash('error', "La cédula {$data['cedula']} no registra votos en el periodo activo.");
                $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
                require_once PROJECT_ROOT . '/src/Views/socios/create.php';
                return;
            }

            if (!$quorum_alcanzado || $aceptados < $req) {
                flash('error', "La persona no puede ser registrada como socio. Motivo: " . (!$quorum_alcanzado ? "No se alcanzó el quórum mínimo de $minimo_votantes votantes." : "No alcanzó el $porcentaje_req% de aprobación ($aceptados/$req requeridos)."));
                $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
                require_once PROJECT_ROOT . '/src/Views/socios/create.php';
                return;
            }
            // ---------------------------------

            // --- VALIDACIÓN DE CÉDULA DUPLICADA ---
            if ($this->socio->existsByCedulaInAcciones($data['cedula'])) {
                flash('error', "La cédula {$data['cedula']} ya se encuentra registrada como socio con otra acción.");
                $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
                require_once PROJECT_ROOT . '/src/Views/socios/create.php';
                return;
            }
            // --------------------------------------

            // Asignar al modelo
            $this->socio->accion = $data['accion'];
            $this->socio->cedula = preg_replace('/[^0-9]/', '', $data['cedula']);
            $this->socio->nacionalidad = $data['nacionalidad'];
            $this->socio->primer_nombre = $data['primer_nombre'];
            $this->socio->segundo_nombre = $data['segundo_nombre'];
            $this->socio->primer_apellido = $data['primer_apellido'];
            $this->socio->segundo_apellido = $data['segundo_apellido'];
            $this->socio->email = $data['email'];
            $this->socio->telefono_movil = $data['telefono_movil'];
            $this->socio->sexo = $data['sexo'];
            $this->socio->fecha_nacimiento = $data['fecha_nacimiento'];
            $this->socio->profesion_id = $data['profesion_id'];
            $this->socio->lugar_trabajo = $data['lugar_trabajo'];
            $this->socio->estado = $data['estado'];
            $this->socio->usuario_refe = $_SESSION['usuario'] ?? 'SYSTEM';

            // Intentar crear
            if ($this->socio->create()) {
                // Eliminar de postulados después de crear socio (ya ha sido admitido y registrado)
                require_once PROJECT_ROOT . '/src/Models/Postulado.php';
                $postulado = new Postulado($this->db);
                $postulado->cedula = $data['cedula'];
                $postulado->delete(); // Elimina de la tabla postulados

                clearOld();
                flash('success', 'Socio creado exitosamente y transferido desde el registro de postulados.');
                redirect('index.php?action=socios');
            } else {
                flash('error', 'Error al crear socio. Verifique si el número de acción o cédula ya existen.');
                $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
                require_once PROJECT_ROOT . '/src/Views/socios/create.php';
            }
        } else {
            clearOld();

            // Obtener admitidos (con votación favorable)
            require_once PROJECT_ROOT . '/src/Models/Votacion.php';
            require_once PROJECT_ROOT . '/src/Models/Configuracion.php';

            $votacionModel = new Votacion($this->db);
            $configModel = new Configuracion($this->db);
            $porcentaje_req = $configModel->get('votacion_porcentaje_aceptacion') ?: 50;
            $minimo_votantes = $configModel->get('votacion_minimo_votantes') ?: 0;

            $stats = $votacionModel->getEstadisticas();
            $quorum_alcanzado = ($stats['votantes_unicos'] >= $minimo_votantes);

            $stmt_vot = $votacionModel->getVotosPorCandidato();
            $todos_postulados = $stmt_vot->fetchAll(PDO::FETCH_ASSOC);

            $admitidos = array_filter($todos_postulados, function ($p) use ($porcentaje_req, $quorum_alcanzado) {
                if (!$quorum_alcanzado)
                    return false;
                $total = (int) $p['votos_totales'];
                $req = ceil(($total * $porcentaje_req) / 100);
                return (int) $p['aceptados'] >= $req && $total > 0;
            });

            // Obtener el próximo número de acción
            $stmt_next = $this->db->query("SELECT MAX(numero_accion) as max FROM acciones");
            $res_next = $stmt_next->fetch(PDO::FETCH_ASSOC);
            $proxima_accion = ($res_next['max'] ?? 0) + 1;

            $profesiones = $this->db->query("SELECT id, nombre FROM profesiones WHERE activo = 1 ORDER BY nombre")->fetchAll();
            require_once PROJECT_ROOT . '/src/Views/socios/create.php';
        }
    }

    /**
     * Muestra el formulario de edición para un socio existente.
     * 
     * @return void
     */
    public function edit()
    {
        $accion = get('accion');
        if ($accion) {
            $this->socio->accion = Validator::clean($accion);
            if ($this->socio->readOne()) {
                require_once PROJECT_ROOT . '/src/Views/socios/edit.php';
            } else {
                flash('error', 'Socio no encontrado.');
                redirect('index.php?action=socios');
            }
        } else {
            flash('error', 'Número de acción no especificado.');
            redirect('index.php?action=socios');
        }
    }

    /**
     * Procesa la actualización de los datos de un socio existente.
     * Valida los datos y verifica que la cédula no se duplique con otra acción.
     * 
     * @return void
     */
    public function update()
    {
        if (isPost()) {
            // Verificar CSRF
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
                redirect('index.php?action=socios');
                return;
            }

            // Limpiar datos
            $data = [
                'accion' => Validator::clean(post('accion')),
                'cedula' => Validator::clean(post('cedula')),
                'primer_nombre' => Validator::clean(post('primer_nombre')),
                'segundo_nombre' => Validator::clean(post('segundo_nombre')),
                'primer_apellido' => Validator::clean(post('primer_apellido')),
                'segundo_apellido' => Validator::clean(post('segundo_apellido')),
                'email' => Validator::clean(post('email')),
                'estado' => Validator::clean(post('estado')),
            ];

            // Guardar datos para repoblar formulario
            setOld($data);

            // Definir reglas de validación
            $rules = [
                'accion' => ['required', 'max:20'],
                'cedula' => ['required', 'venezuelan_id'],
                'primer_nombre' => ['required', 'min:2', 'max:100'],
                'segundo_nombre' => ['max:100'],
                'primer_apellido' => ['required', 'min:2', 'max:100'],
                'segundo_apellido' => ['max:100'],
                'estado' => ['required'],
            ];

            // Validar
            if (!Validator::validate($rules, $data)) {
                flash('error', 'Por favor corrija los errores en el formulario.');
                $this->socio->accion = $data['accion'];
                $this->socio->readOne();
                require_once PROJECT_ROOT . '/src/Views/socios/edit.php';
                return;
            }

            // --- VALIDACIÓN DE CÉDULA DUPLICADA ---
            if ($this->socio->existsByCedulaInAcciones($data['cedula'], $data['accion'])) {
                flash('error', "La cédula {$data['cedula']} ya se encuentra registrada para otro socio.");
                $this->socio->accion = $data['accion'];
                $this->socio->readOne();
                require_once PROJECT_ROOT . '/src/Views/socios/edit.php';
                return;
            }
            // --------------------------------------

            // Asignar al modelo
            $this->socio->accion = $data['accion'];
            $this->socio->cedula = preg_replace('/[^0-9]/', '', $data['cedula']);
            $this->socio->primer_nombre = $data['primer_nombre'];
            $this->socio->segundo_nombre = $data['segundo_nombre'];
            $this->socio->primer_apellido = $data['primer_apellido'];
            $this->socio->segundo_apellido = $data['segundo_apellido'];
            $this->socio->email = $data['email'];
            $this->socio->estado = $data['estado'];

            // Intentar actualizar
            if ($this->socio->update()) {
                clearOld();
                flash('success', 'Socio actualizado exitosamente.');
                redirect('index.php?action=socios');
            } else {
                flash('error', 'Error al actualizar socio.');
                $this->socio->readOne();
                require_once PROJECT_ROOT . '/src/Views/socios/edit.php';
            }
        } else {
            redirect('index.php?action=socios');
        }
    }

    /**
     * Elimina un socio (acción) del sistema.
     * 
     * @return void
     */
    public function delete()
    {
        if (!isPost()) {
            flash('error', 'Método no permitido.');
            redirect('index.php?action=socios');
            return;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
            redirect('index.php?action=socios');
            return;
        }

        $accion = post('accion');
        if ($accion) {
            $this->socio->accion = Validator::clean($accion);
            if ($this->socio->delete()) {
                flash('success', 'Socio eliminado exitosamente.');
                redirect('index.php?action=socios');
            } else {
                flash('error', 'Error al eliminar socio.');
                redirect('index.php?action=socios');
            }
        } else {
            flash('error', 'Número de acción no especificado.');
            redirect('index.php?action=socios');
        }
    }

    /**
     * Cambia el estado de un socio (ej. ACTIVO, INACTIVO, SUSPENDIDO).
     * 
     * @return void
     */
    public function changeStatus()
    {
        if (!isPost()) {
            flash('error', 'Método no permitido.');
            redirect('index.php?action=socios');
            return;
        }

        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            flash('error', 'Token de seguridad inválido. Por favor intente nuevamente.');
            redirect('index.php?action=socios');
            return;
        }

        $accion = post('accion');
        $status = post('status');

        if ($accion && $status) {
            $this->socio->accion = Validator::clean($accion);
            if ($this->socio->setStatus($status)) {
                flash('success', "Estado de la acción {$accion} actualizado a {$status}.");
            } else {
                flash('error', "No se pudo actualizar el estado.");
            }
        }

        redirect('index.php?action=socios');
    }
}
?>