<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';
?>

<!-- Vue.js Application Root -->
<div id="backupApp" v-cloak>
    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo Csrf::getToken(); ?>">

    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-database text-success mr-2"></i>Respaldo de Base de Datos
        </h1>
        <div>
            <button class="btn btn-sm btn-outline-secondary mr-2" @click="loadBackups" :disabled="loadingBackups">
                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingBackups }"></i> Actualizar
            </button>
            <button class="btn btn-sm btn-success shadow-sm" @click="createBackup" :disabled="creatingBackup">
                <i class="fas fa-download fa-sm text-white-50 mr-1"></i>
                <span v-if="creatingBackup">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Generando...
                </span>
                <span v-else>Generar Respaldo</span>
            </button>
        </div>
    </div>

    <!-- Status Alert -->
    <div v-if="backupAlert.show" class="alert alert-dismissible fade show" :class="'alert-' + backupAlert.type" role="alert">
        <i class="fas mr-2" :class="backupAlert.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
        <span v-text="backupAlert.message"></span>
        <button type="button" class="close" @click="backupAlert.show = false">
            <span>&times;</span>
        </button>
    </div>

    <!-- Progress bar while creating backup -->
    <div v-if="creatingBackup" class="mb-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-cog fa-spin text-success mr-2"></i>
                    <span class="text-muted">Ejecutando mysqldump... Esto puede tardar unos segundos.</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Respaldos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" v-text="backups.length"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-archive fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Espacio Utilizado</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" v-text="totalBackupSize"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Último Respaldo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" v-text="lastBackupDate"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>Historial de Respaldos
            </h6>
        </div>
        <div class="card-body">
            <!-- Backups Table -->
            <div class="table-responsive" v-if="backups.length > 0">
                <table class="table table-hover table-bordered" id="backupTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 35%"><i class="fas fa-file-code mr-1"></i>Archivo</th>
                            <th style="width: 15%"><i class="fas fa-calendar-alt mr-1"></i>Fecha</th>
                            <th style="width: 15%"><i class="fas fa-weight-hanging mr-1"></i>Tamaño</th>
                            <th style="width: 30%" class="text-center"><i class="fas fa-cog mr-1"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(backup, index) in backups" :key="backup.filename">
                            <td class="align-middle">
                                <span class="badge badge-secondary" v-text="index + 1"></span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-alt text-success mr-2"></i>
                                    <div>
                                        <span class="font-weight-bold text-gray-800" style="font-size: 0.85rem;" v-text="backup.filename"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <small class="text-muted" v-text="backup.date"></small>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-info badge-pill px-3 py-2" v-text="backup.size"></span>
                            </td>
                            <td class="align-middle text-center" style="white-space: nowrap;">
                                <div class="d-flex justify-content-center">
                                    <a :href="'index.php?action=backup_download&file=' + encodeURIComponent(backup.filename)" 
                                       class="btn btn-sm btn-info btn-icon-split shadow-sm mr-2" title="Descargar">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-download"></i>
                                        </span>
                                        <span class="text">Descargar</span>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-icon-split shadow-sm" 
                                            @click="deleteBackup(backup.filename)" 
                                            :disabled="deletingBackup === backup.filename" title="Eliminar">
                                        <span class="icon text-white-50">
                                            <i class="fas" :class="deletingBackup === backup.filename ? 'fa-spinner fa-spin' : 'fa-trash-alt'"></i>
                                        </span>
                                        <span class="text">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loadingBackups" class="text-center py-5">
                <i class="fas fa-database fa-3x text-gray-300 mb-3"></i>
                <p class="text-muted mb-0">No hay respaldos disponibles.</p>
                <small class="text-muted">Haz clic en "Generar Respaldo" para crear el primero.</small>
            </div>

            <!-- Loading State -->
            <div v-if="loadingBackups" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                <p class="text-muted mb-0">Cargando respaldos...</p>
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
            const backups = ref([]);
            const loadingBackups = ref(false);
            const creatingBackup = ref(false);
            const deletingBackup = ref('');
            const backupAlert = ref({ show: false, type: 'success', message: '' });

            const showAlert = (type, message) => {
                backupAlert.value = { show: true, type, message };
                setTimeout(() => {
                    backupAlert.value.show = false;
                }, 5000);
            };

            const loadBackups = async () => {
                loadingBackups.value = true;
                try {
                    const response = await fetch('index.php?action=backup_list');
                    const data = await response.json();
                    if (data.success) {
                        backups.value = data.backups;
                    }
                } catch (e) {
                    console.error('Error loading backups:', e);
                } finally {
                    loadingBackups.value = false;
                }
            };

            const createBackup = async () => {
                if (creatingBackup.value) return;
                
                creatingBackup.value = true;
                backupAlert.value.show = false;

                try {
                    const response = await fetch('index.php?action=backup_create');
                    const data = await response.json();
                    
                    if (data.success) {
                        showAlert('success', `${data.message} — ${data.filename} (${data.size})`);
                        await loadBackups();
                    } else {
                        showAlert('danger', data.message + (data.details ? ': ' + data.details : ''));
                    }
                } catch (e) {
                    showAlert('danger', 'Error de conexión al generar el respaldo.');
                    console.error('Backup error:', e);
                } finally {
                    creatingBackup.value = false;
                }
            };

            const deleteBackup = async (filename) => {
                if (!confirm('¿Está seguro de que desea eliminar este respaldo?\n\n' + filename)) return;
                
                deletingBackup.value = filename;
                try {
                    const token = document.getElementById('csrf_token').value;
                    const response = await fetch('index.php?action=backup_delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'filename=' + encodeURIComponent(filename) + '&csrf_token=' + encodeURIComponent(token)
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        showAlert('success', data.message);
                        await loadBackups();
                    } else {
                        showAlert('danger', data.message);
                    }
                } catch (e) {
                    showAlert('danger', 'Error de conexión al eliminar el respaldo.');
                } finally {
                    deletingBackup.value = '';
                }
            };

            const totalBackupSize = computed(() => {
                const totalBytes = backups.value.reduce((sum, b) => sum + (b.sizeBytes || 0), 0);
                if (totalBytes >= 1073741824) return (totalBytes / 1073741824).toFixed(2) + ' GB';
                if (totalBytes >= 1048576) return (totalBytes / 1048576).toFixed(2) + ' MB';
                if (totalBytes >= 1024) return (totalBytes / 1024).toFixed(2) + ' KB';
                return totalBytes + ' B';
            });

            const lastBackupDate = computed(() => {
                if (backups.value.length === 0) return 'Ninguno';
                return backups.value[0].date;
            });

            onMounted(() => {
                loadBackups();
            });

            return {
                backups,
                loadingBackups,
                creatingBackup,
                deletingBackup,
                backupAlert,
                totalBackupSize,
                lastBackupDate,
                loadBackups,
                createBackup,
                deleteBackup
            }
        }
    }).mount('#backupApp');
</script>

<style>
    [v-cloak] { display: none; }
    #backupTable tbody tr {
        transition: background-color 0.15s ease;
    }
    #backupTable tbody tr:hover {
        background-color: rgba(28, 200, 138, 0.05);
    }
    .alert {
        border-radius: 0.5rem;
    }
</style>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
