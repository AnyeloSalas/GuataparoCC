<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$user = [
    'name' => $_SESSION['usuario'],
    'role' => strtoupper($_SESSION['tipousuario'])
];

require_once __DIR__ . '/../src/Views/layouts/header.php';
require_once __DIR__ . '/../src/Views/layouts/sidebar.php';
require_once __DIR__ . '/../src/Views/layouts/topbar.php';
?>

<!-- Vue.js Application Root -->
<div id="app" v-cloak>
    
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-left-primary bg-white py-3 px-4 rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">{{ greeting }}, {{ user.name }}</h1>
                        <p class="text-muted mb-0">Bienvenido al Centro de Control de Guataparo Country Club.</p>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill shadow-sm">
                            <i class="fas fa-calendar-alt mr-1"></i> {{ currentDate }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row mb-5">
        <!-- Dashboard Modules Section -->
        <div :class="user.role !== 'SOCIO' ? 'col-lg-9' : 'col-12'">
            <h6 class="font-weight-bold text-primary text-uppercase mb-3">Módulos Disponibles</h6>
            <div class="row g-3">
                <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-3" v-for="module in filteredModules" :key="module.id">
                    <div class="card shadow hover-card h-100 border-0 border-top border-4" :class="`border-${module.color}`">
                        <div class="card-body p-3 text-center">
                            <div class="icon-circle mb-2 mx-auto" :class="`bg-${module.color}`">
                                <i class="fas text-white" :class="module.icon"></i>
                            </div>
                            <div class="text-xs font-weight-bold text-gray-500 text-uppercase mb-1" style="font-size: 0.65rem;" v-text="module.category"></div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800" :style="{ fontSize: module.title.length > 11 ? '0.75rem' : '0.85rem', whiteSpace: 'nowrap' }" v-text="module.title"></div>
                            <a :href="module.link" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Section (Activity) -->
        <div class="col-lg-3" v-if="user.role !== 'SOCIO'">
            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                </div>
                <div class="card-body p-0">
                    <div v-if="actividad.length === 0" class="p-4 text-center text-muted small">
                        Sin actividad reciente registrada.
                    </div>
                    <div v-else class="list-group list-group-flush small">
                        <div v-for="(act, index) in actividad" :key="index" class="list-group-item list-group-item-action border-0 px-3 py-2">
                            <div class="d-flex w-100 justify-content-between mb-1">
                                <span class="font-weight-bold text-dark">{{ act.usuario }}</span>
                                <span class="text-muted text-xs">{{ act.fecha }}</span>
                            </div>
                            <p class="mb-0 text-gray-600">{{ act.descripcion }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 text-center bg-light">
                    <a href="index.php?action=auditoria" class="text-xs font-weight-bold text-primary text-uppercase">Ver todo el historial</a>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- End Vue App -->


<!-- Load Vue.js (Local) -->
<script src="assets/vendor/js/vue.global.prod.js"></script>

<script>
    const { createApp, ref, computed, onMounted } = Vue;

    createApp({
        setup() {
            // User Data passed from PHP
            const user = ref(<?php echo json_encode($user); ?>);
            
            // Dynamic permissions from sidebar
            const permissions = ref(<?php echo json_encode(isset($permisos_usuario_sidebar) ? $permisos_usuario_sidebar : []); ?>);
            
            // Dashboard Stats
            const dashboardStats = ref({ socios: 0, votos: 0, total_votos: 0, participacion: 0, usuarios: 0 });
            const actividad = ref([]);
            
            const loadingMetrics = ref(false);

            // Time and Greeting logic
            const currentDate = ref(new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
            const greeting = computed(() => {
                const hour = new Date().getHours();
                if (hour < 12) return 'Buenos días';
                if (hour < 18) return 'Buenas tardes';
                return 'Buenas noches';
            });

            const loadDashboardData = async () => {
                try {
                    const response = await fetch('index.php?action=dashboard_stats');
                    const data = await response.json();
                    if (data.success) {
                        dashboardStats.value = data.stats;
                        actividad.value = data.actividad;
                    }
                } catch (e) { console.error('Error stats:', e); }
            };

            onMounted(() => {
                loadDashboardData();
            });

            // Dashboard Configuration
            const modules = ref([
                {
                    id: 1,
                    title: 'Socios',
                    category: 'Gestión',
                    icon: 'fa-users',
                    color: 'primary',
                    link: 'index.php?action=socios',
                    action_name: 'socios',
                    roles: ['ADMIN', 'SUPERADMIN', 'OPERADOR']
                },
                {
                    id: 2,
                    title: 'Usuarios',
                    category: 'Seguridad',
                    icon: 'fa-user-lock',
                    color: 'success',
                    link: 'index.php?action=usuarios',
                    action_name: 'usuarios',
                    roles: ['ADMIN', 'SUPERADMIN']
                },
                {
                    id: 3,
                    title: 'Cónyuges',
                    category: 'Familiares',
                    icon: 'fa-heart',
                    color: 'info',
                    link: 'index.php?action=conyugues',
                    action_name: 'conyugues',
                    roles: ['ADMIN', 'SUPERADMIN', 'OPERADOR']
                },
                {
                    id: 4,
                    title: 'Votaciones',
                    category: 'Elecciones',
                    icon: 'fa-vote-yea',
                    color: 'warning',
                    link: 'index.php?action=votaciones',
                    action_name: 'votaciones',
                    roles: ['ADMIN', 'SUPERADMIN', 'OPERADOR', 'SOCIO']
                },
                {
                    id: 5,
                    title: 'Reportes',
                    category: 'Estadísticas',
                    icon: 'fa-chart-bar',
                    color: 'danger',
                    link: 'index.php?action=reportes',
                    action_name: 'reportes',
                    roles: ['ADMIN', 'SUPERADMIN']
                },
                {
                    id: 6,
                    title: 'Configuración',
                    category: 'Sistema',
                    icon: 'fa-sliders-h',
                    color: 'secondary',
                    link: 'index.php?action=configuracion',
                    action_name: 'configuracion',
                    roles: ['ADMIN', 'SUPERADMIN']
                },
                {
                    id: 7,
                    title: 'Auditoría',
                    category: 'Seguimiento',
                    icon: 'fa-history',
                    color: 'dark',
                    link: 'index.php?action=auditoria',
                    action_name: 'auditoria',
                    roles: ['ADMIN', 'SUPERADMIN']
                },
                {
                    id: 8,
                    title: 'Documentos',
                    category: 'Archivos',
                    icon: 'fa-file-alt',
                    color: 'info',
                    link: 'index.php?action=documentos',
                    action_name: 'documentos',
                    roles: ['ADMIN', 'SUPERADMIN', 'OPERADOR', 'SOCIO']
                },
                {
                    id: 9,
                    title: 'Empresas',
                    category: 'Entidades',
                    icon: 'fa-landmark',
                    color: 'primary',
                    link: 'index.php?action=empresas',
                    action_name: 'empresas',
                    roles: ['ADMIN', 'SUPERADMIN']
                }
            ]);

            const filteredModules = computed(() => {
                if (!user.value || !user.value.role) return [];
                return modules.value.filter(module => {
                    const role = user.value.role;
                    if (role === 'SOCIO') {
                        return module.action_name === 'votaciones' || module.action_name === 'documentos';
                    }
                    if (role === 'ADMIN' || role === 'ADMINISTRADOR' || role === 'SUPERADMIN') return true;
                    return Array.isArray(permissions.value) && permissions.value.includes(module.action_name);
                });
            });

            return {
                user,
                dashboardStats,
                actividad,
                loadingMetrics,
                filteredModules,
                greeting,
                currentDate
            }
        }
    }).mount('#app');
</script>

<style>
    body, #wrapper, #content-wrapper, #content {
        background-image: linear-gradient(rgba(244, 247, 246, 0.25), rgba(244, 247, 246, 0.25)), url('assets/img/img13.JPG') !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
    }
    [v-cloak] { display: none; }
    
    .hover-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px !important;
    }
    .hover-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .list-group-item-action:hover {
        background-color: rgba(0, 26, 114, 0.05) !important;
    }
</style>

<?php
require_once __DIR__ . '/../src/Views/layouts/footer.php';
?>
