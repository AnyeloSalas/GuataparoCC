<?php
// Script de verificación de huellas en base de datos
// Acceder desde: http://localhost/php_system/public/verificar_huellas.php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Huellas - Base de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .warning { color: #ff6b6b; font-weight: bold; }
        .success { color: #51cf66; font-weight: bold; }
        .card { margin-bottom: 20px; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mb-4">🔍 Verificación de Huellas en Base de Datos</h1>
        
        <!-- USUARIOS -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">📋 USUARIOS</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Usuario</th>
                                <th>Cédula</th>
                                <th>¿Tiene Huella?</th>
                                <th>Longitud (bytes)</th>
                                <th>Estado</th>
                                <th>Muestra Template</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT usuario, cedula, 
                                      CHAR_LENGTH(huella_template) as longitud_huella,
                                      IF(huella_template IS NULL OR huella_template = '', 'NO', 'SI') as tiene_huella,
                                      SUBSTRING(huella_template, 1, 60) as muestra_huella
                                      FROM usuarios 
                                      ORDER BY usuario";
                            
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($usuarios as $usuario) {
                                $longitud = $usuario['longitud_huella'] ?? 0;
                                $estado = '';
                                $class = '';
                                
                                if ($usuario['tiene_huella'] === 'SI') {
                                    if ($longitud < 100) {
                                        $estado = '⚠️ Template muy corto (ERROR)';
                                        $class = 'table-warning';
                                    } else {
                                        $estado = '✅ OK';
                                        $class = 'table-success';
                                    }
                                } else {
                                    $estado = '❌ Sin huella';
                                    $class = 'table-light';
                                }
                                
                                echo "<tr class='$class'>";
                                echo "<td><strong>" . htmlspecialchars($usuario['usuario']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($usuario['cedula']) . "</td>";
                                echo "<td>" . $usuario['tiene_huella'] . "</td>";
                                echo "<td>" . $longitud . "</td>";
                                echo "<td>$estado</td>";
                                $muestraHuella = $usuario['muestra_huella'] ?? '';
                                $muestraTexto = $muestraHuella ? htmlspecialchars(substr($muestraHuella, 0, 40)) : 'N/A';
                                echo "<td><small><code>$muestraTexto...</code></small></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SOCIOS -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">👥 SOCIOS (PERSONAS) - Muestra de 10</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>¿Tiene Huella?</th>
                                <th>Longitud (bytes)</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query2 = "SELECT CONCAT(nombres, ' ', apellidos) as nombre, cedula, 
                                       CHAR_LENGTH(huella_template) as longitud_huella,
                                       IF(huella_template IS NULL OR huella_template = '', 'NO', 'SI') as tiene_huella
                                       FROM personas 
                                       LIMIT 10";
                            
                            try {
                                $stmt2 = $db->prepare($query2);
                                $stmt2->execute();
                                $socios = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($socios as $socio) {
                                    $longitud = $socio['longitud_huella'] ?? 0;
                                    $estado = '';
                                    $class = '';
                                    
                                    if ($socio['tiene_huella'] === 'SI') {
                                        if ($longitud < 100) {
                                            $estado = '⚠️ Template muy corto (ERROR)';
                                            $class = 'table-warning';
                                        } else {
                                            $estado = '✅ OK';
                                            $class = 'table-success';
                                        } 
                                    } else {
                                        $estado = '❌ Sin huella';
                                        $class = 'table-light';
                                    }
                                    
                                    echo "<tr class='$class'>";
                                    echo "<td>" . htmlspecialchars($socio['nombre']) . "</td>";
                                    echo "<td>" . htmlspecialchars($socio['cedula']) . "</td>";
                                    echo "<td>" . $socio['tiene_huella'] . "</td>";
                                    echo "<td>" . $longitud . "</td>";
                                    echo "<td>$estado</td>";
                                    echo "</tr>";
                                }
                            } catch (Exception $e) {
                                echo "<tr><td colspan='5' class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">📊 ESTADÍSTICAS GENERALES</h3>
            </div>
            <div class="card-body">
                <?php
                $stats = $db->query("SELECT 
                    COUNT(*) as total,
                    SUM(IF(huella_template IS NOT NULL AND huella_template != '', 1, 0)) as con_huella,
                    SUM(IF(CHAR_LENGTH(huella_template) < 100 AND huella_template IS NOT NULL, 1, 0)) as con_error,
                    AVG(CHAR_LENGTH(huella_template)) as longitud_promedio
                    FROM usuarios")->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <h4><?= $stats['total'] ?></h4>
                            <p class="mb-0">Total Usuarios</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <h4><?= $stats['con_huella'] ?></h4>
                            <p class="mb-0">Con Huella Registrada</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <h4><?= $stats['con_error'] ?></h4>
                            <p class="mb-0">Con Templates Cortos (Error)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <h4><?= number_format($stats['longitud_promedio'] ?? 0, 0) ?> bytes</h4>
                            <p class="mb-0">Longitud Promedio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUDITORÍA -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h3 class="mb-0">📝 ÚLTIMOS 10 REGISTROS DE AUDITORÍA</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Tipo Evento</th>
                                <th>Usuario</th>
                                <th>Cédula</th>
                                <th>Similitud</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $auditQuery = "SELECT * FROM auditoria_huellas 
                                               ORDER BY fecha_hora DESC 
                                               LIMIT 10";
                                $auditStmt = $db->prepare($auditQuery);
                                $auditStmt->execute();
                                $audits = $auditStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($audits as $audit) {
                                    $class = '';
                                    if (strpos($audit['tipo_evento'], 'exitoso') !== false) {
                                        $class = 'table-success';
                                    } elseif (strpos($audit['tipo_evento'], 'fallido') !== false) {
                                        $class = 'table-danger';
                                    }
                                    
                                    echo "<tr class='$class'>";
                                    echo "<td><small>" . htmlspecialchars($audit['fecha_hora']) . "</small></td>";
                                    echo "<td><strong>" . htmlspecialchars($audit['tipo_evento']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($audit['nombre_usuario'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($audit['cedula_usuario'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($audit['similitud'] ?? 'N/A') . "</td>";
                                    echo "<td><small>" . htmlspecialchars($audit['mensaje']) . "</small></td>";
                                    echo "</tr>";
                                }
                            } catch (Exception $e) {
                                echo "<tr><td colspan='6' class='text-danger'>Tabla de auditoría no disponible: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DIAGNÓSTICO -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="mb-0">🔧 DIAGNÓSTICO Y RECOMENDACIONES</h3>
            </div>
            <div class="card-body">
                <?php
                $problemas = [];
                
                // Verificar templates cortos
                if ($stats['con_error'] > 0) {
                    $problemas[] = "<strong>⚠️ Se encontraron {$stats['con_error']} templates con longitud menor a 100 bytes.</strong><br>
                    Esto indica que la captura no está funcionando correctamente. Los templates deberían tener al menos 500-1000 bytes.";
                }
                
                // Verificar si el bridge está capturando
                $logFile = __DIR__ . '/../logs/huella_debug.log';
                if (file_exists($logFile)) {
                    $lastLines = array_slice(file($logFile), -5);
                    $hasRecent = false;
                    foreach ($lastLines as $line) {
                        if (strpos($line, date('Y-m-d')) !== false) {
                            $hasRecent = true;
                            break;
                        }
                    }
                    
                    if (!$hasRecent) {
                        $problemas[] = "<strong>⚠️ No hay registros recientes en el log de huellas.</strong><br>
                        Esto podría indicar que el sistema no está recibiendo solicitudes de captura.";
                    }
                }
                
                if (count($problemas) > 0) {
                    echo "<div class='alert alert-danger'>";
                    echo "<h4>Problemas Detectados:</h4>";
                    echo "<ul>";
                    foreach ($problemas as $problema) {
                        echo "<li>$problema</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                    
                    echo "<div class='alert alert-info'>";
                    echo "<h4>Acciones Recomendadas:</h4>";
                    echo "<ol>";
                    echo "<li>Verificar que <code>run_cpp_bridge.bat</code> esté ejecutándose</li>";
                    echo "<li>Revisar el log del bridge: <code>cpp_bridge/cpp_status.log</code></li>";
                    echo "<li>Verificar que el sensor de huella esté conectado correctamente</li>";
                    echo "<li>Intentar re-capturar las huellas con templates cortos</li>";
                    echo "<li>Revisar el archivo: <code>logs/huella_debug.log</code> para más detalles</li>";
                    echo "</ol>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-success'>";
                    echo "<h4>✅ No se detectaron problemas críticos</h4>";
                    echo "<p class='mb-0'>El sistema de huellas parece estar funcionando correctamente.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <button onclick="location.reload()" class="btn btn-primary btn-lg">🔄 Actualizar</button>
            <a href="dashboard.php" class="btn btn-secondary btn-lg">← Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>
