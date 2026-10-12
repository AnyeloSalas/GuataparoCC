<?php
// Script para configurar Hikvision como driver de huella
// Ejecutar desde: http://localhost/php_system/public/configurar_hikvision.php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Configurar Hikvision</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 40px; background: #f5f5f5; }
        .card { max-width: 800px; margin: 0 auto; }
        pre { background: #2d2d2d; color: #51cf66; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h3 class='mb-0'>🔧 Configuración de Sensor Hikvision DS-K1F820-F</h3>
        </div>
        <div class='card-body'>";

try {
    // 1. Verificar si existe la tabla configuracion
    $tableCheck = $db->query("SHOW TABLES LIKE 'configuracion'")->fetch();
    
    if (!$tableCheck) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>⚠️ Tabla 'configuracion' no existe</h4>";
        echo "<p>Creando tabla...</p>";
        echo "</div>";
        
        $db->exec("CREATE TABLE IF NOT EXISTS configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT,
            descripcion TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        echo "<div class='alert alert-success'>✅ Tabla creada exitosamente</div>";
    }
    
    // 2. Configurar driver Hikvision
    echo "<h4 class='mt-3'>📝 Configurando parámetros...</h4>";
    
    $configs = [
        ['huella_driver', 'hikvision', 'Driver de huella digital a utilizar'],
        ['huella_hik_ip', '192.0.0.64', 'IP del dispositivo Hikvision'],
        ['huella_hik_port', '8000', 'Puerto del dispositivo Hikvision'],
        ['huella_hik_user', 'admin', 'Usuario del dispositivo Hikvision'],
        ['huella_hik_pass', '12345', 'Contraseña del dispositivo Hikvision']
    ];
    
    foreach ($configs as $config) {
        list($clave, $valor, $descripcion) = $config;
        
        // Verificar si existe
        $stmt = $db->prepare("SELECT id FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Actualizar
            $stmt = $db->prepare("UPDATE configuracion SET valor = ?, descripcion = ? WHERE clave = ?");
            $stmt->execute([$valor, $descripcion, $clave]);
            echo "<div class='alert alert-info'>";
            echo "🔄 Actualizado: <strong>$clave</strong> = $valor";
            echo "</div>";
        } else {
            // Insertar
            $stmt = $db->prepare("INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$clave, $valor, $descripcion]);
            echo "<div class='alert alert-success'>";
            echo "✅ Creado: <strong>$clave</strong> = $valor";
            echo "</div>";
        }
    }
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>✅ Configuración Completada</h4>";
    echo "<p>El sistema ahora está configurado para usar el sensor Hikvision DS-K1F820-F</p>";
    echo "</div>";
    
    // 3. Mostrar configuración actual
    echo "<h4 class='mt-4'>📋 Configuración Actual:</h4>";
    echo "<table class='table table-bordered'>";
    echo "<thead class='thead-dark'>";
    echo "<tr><th>Clave</th><th>Valor</th><th>Descripción</th></tr>";
    echo "</thead><tbody>";
    
    $stmt = $db->query("SELECT * FROM configuracion WHERE clave LIKE 'huella%' ORDER BY clave");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><code>{$row['clave']}</code></td>";
        echo "<td><strong>{$row['valor']}</strong></td>";
        echo "<td>{$row['descripcion']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // 4. Instrucciones de prueba
    echo "<div class='alert alert-warning mt-4'>";
    echo "<h4>🧪 Próximos Pasos:</h4>";
    echo "<ol>";
    echo "<li><strong>Verificar que el sensor esté conectado por USB</strong></li>";
    echo "<li><strong>Probar captura:</strong> ";
    echo "<a href='http://localhost:8888/capture?driver=hikvision&ip=192.0.0.64&port=8000&user=admin&pass=12345' target='_blank' class='btn btn-sm btn-primary'>Test Captura</a>";
    echo "</li>";
    echo "<li>Si funciona, intenta registrar una huella en el sistema</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='alert alert-info'>";
    echo "<h5>📝 Nota sobre IP:</h5>";
    echo "<p>El sensor Hikvision DS-K1F820-F USB usa la IP virtual <code>192.0.0.64</code> por defecto.</p>";
    echo "<p>Si no funciona, verifica en el Administrador de Dispositivos la IP asignada al adaptador de red virtual Hikvision.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "        </div>
        <div class='card-footer'>
            <a href='dashboard.php' class='btn btn-secondary'>← Volver al Dashboard</a>
            <a href='verificar_huellas.php' class='btn btn-info'>Ver Estado de Huellas</a>
        </div>
    </div>
</body>
</html>";
?>
