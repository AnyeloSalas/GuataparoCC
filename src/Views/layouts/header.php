<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper to load classes safely if they aren't already loaded
// We use a closure or immediate execution to avoid polluting global namespace with function definitions if included multiple times
$loadConfig = function() {
    $root = dirname(dirname(dirname(__DIR__))); // c:/laragon/www/php_system
    
    if (!class_exists('Database')) {
        $dbPath = $root . '/config/database.php';
        if (file_exists($dbPath)) require_once $dbPath;
    }
    
    if (!class_exists('Configuracion')) {
        $modelPath = $root . '/src/Models/Configuracion.php';
        if (file_exists($modelPath)) require_once $modelPath;
    }
};
$loadConfig();

$colorPrimario = '#01091fff'; // SB Admin 2 Default
$colorLetra = '#858796'; // SB Admin 2 Default text color
$sistemaNombre = 'Sistema Guataparo';

if (class_exists('Database') && class_exists('Configuracion')) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $config = new Configuracion($db);
        
        if ($val = $config->getColorPrimario()) {
            $colorPrimario = $val;
        }
        if ($val = $config->getColorLetra()) {
            $colorLetra = $val;
        }
        if ($val = $config->getSistemaNombre()) {
            $sistemaNombre = $val;
        }
    } catch (Exception $e) {
        // Fallback to defaults
    }
}

// 1. Prevent Browser Caching (Crucial for "Back" button security)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Prevent caching Meta tags -->
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">

    <script>
    /**
     * Requirement: Close session or force login when navigating back.
     * This script detects if the page is being loaded from the browser's 
     * back-forward cache (bfcache). If so, it forces a reload.
     */
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (typeof window.performance != 'undefined' && window.performance.navigation.type === 2)) {
            // event.persisted is true if loaded from cache
            // performance.navigation.type === 2 means TYPE_BACK_FORWARD
            window.location.reload();
        }
    });
    </script>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo htmlspecialchars($sistemaNombre); ?>">
    <meta name="author" content="">

    <title><?php echo htmlspecialchars($sistemaNombre); ?></title>

    <!-- Custom fonts for this template (Local) -->
    <link href="assets/vendor/css/fontawesome.min.css" rel="stylesheet" type="text/css">
    <link href="assets/vendor/css/nunito.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap-icons.css">

    <!-- Custom styles for this template (Local) -->
    <link href="assets/vendor/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- DataTables CSS (Local) -->
    <link href="assets/vendor/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- Dynamic Styles from Configuration -->
    <style>
        :root {
            --primary-color: <?php echo $colorPrimario; ?>;
            --bg-color: <?php echo $bgDashboard; ?>;
            --text-main: #001a72;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-color) !important;
        }

        #content-wrapper {
            background-color: var(--bg-color) !important;
            background-image: radial-gradient(#d1d5db 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .text-gray-800, .text-gray-900 {
            color: var(--text-main) !important;
        }

        /* Premium Card Styles */
        .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
        }

        .bg-gradient-primary {
            background-color: var(--primary-color) !important;
            background-image: linear-gradient(180deg, var(--primary-color) 10%, #00135a 100%) !important;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            font-weight: 700;
            border-radius: 8px;
        }

        .border-left-primary, 
        .border-left-success, 
        .border-left-info, 
        .border-left-warning, 
        .border-left-danger { 
            border-left: .25rem solid var(--primary-color) !important; 
        }

        /* Unify Text Colors in Cards */
        .text-primary, .text-success, .text-info, .text-warning, .text-danger {
            color: var(--primary-color) !important;
        }

        /* Unify Background Colors for progress bars, etc. */
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger {
            background-color: var(--primary-color) !important;
        }

        /* Global Table Styles */
        .table thead th {
            background-color: #f8fafc;
            color: var(--primary-color);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #edf2f7;
            padding: 15px;
        }

        .table td {
            vertical-align: middle !important;
            padding: 12px 15px;
            color: #4a5568;
            font-size: 0.9rem;
        }

        /* Global Form Styles */
        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            height: auto;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 26, 114, 0.1);
        }

        /* Global Button Styles */
        .btn {
            border-radius: 8px;
            font-weight: 700;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 5px 15px;
            font-size: 0.8rem;
        }

        .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
        }

        .btn-success:hover {
            background-color: #059669 !important;
            transform: translateY(-1px);
        }

        /* Global Alert Styles */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .alert-success { background-color: #ecfdf5; color: #065f46; }
        .alert-danger { background-color: #fef2f2; color: #991b1b; }
        .alert-warning { background-color: #fffbeb; color: #92400e; }
        .alert-info { background-color: #eff6ff; color: #1e40af; }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .h3, h3 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }

            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }


            /* Adjust chart height on mobile */
            .chart-area, .chart-pie {
                height: 250px !important;
            }
            
            /* Better table scrolling */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Stack buttons in tables if needed or add spacing */
            .btn-group-sm > .btn, .btn-sm {
                padding: .25rem .5rem;
                font-size: .875rem;
            }
        }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
