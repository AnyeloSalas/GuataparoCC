<?php
// Script para configurar Hikvision USB (DS-K1F820-F)
// Ejecutar desde: http://localhost/php_system/public/configurar_hikvision_usb.php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Configurar Hikvision USB</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 40px; background: #f5f5f5; }
        .card { max-width: 800px; margin: 0 auto; }
        pre { background: #2d2d2d; color: #51cf66; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='card'>
        <div class='card-header bg-success text-white'>
            <h3 class='mb-0'>🔌 Configuración Sensor USB Hikvision DS-K1F820-F</h3>
        </div>
        <div class='card-body'>";

try {
    // 1. Verificar si existe la tabla configuracion
    $tableCheck = $db->query("SHOW TABLES LIKE 'configuracion'")->fetch();
    
    if (!$tableCheck) {
        $db->exec("CREATE TABLE IF NOT EXISTS configuracion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT,
            descripcion TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        echo "<div class='alert alert-success'>✅ Tabla de configuración creada</div>";
    }
    
    // 2. Configurar driver Hikvision USB
    echo "<h4 class='mt-3'>📝 Configurando parámetros USB...</h4>";
    
    $configs = [
        ['huella_driver', 'hikvision_usb', 'Driver para sensor Hikvision USB (DS-K1F820-F)'],
        // Estos no se usan en USB pero se dejan por compatibilidad (opcionales)
        ['huella_hik_ip', '127.0.0.1', 'IP (No usada en modo USB)'],
        ['huella_hik_port', '8000', 'Puerto (No usado en modo USB)'],
        ['huella_hik_user', 'admin', 'Usuario (No usado en modo USB)'],
        ['huella_hik_pass', '12345', 'Contraseña (No usada en modo USB)']
    ];
    
    foreach ($configs as $config) {
        list($clave, $valor, $descripcion) = $config;
        
        $stmt = $db->prepare("SELECT id FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            $stmt = $db->prepare("UPDATE configuracion SET valor = ?, descripcion = ? WHERE clave = ?");
            $stmt->execute([$valor, $descripcion, $clave]);
            echo "<div class='alert alert-info'>🔄 Actualizado: <strong>$clave</strong> = $valor</div>";
        } else {
            $stmt = $db->prepare("INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$clave, $valor, $descripcion]);
            echo "<div class='alert alert-success'>✅ Creado: <strong>$clave</strong> = $valor</div>";
        }
    }
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>✅ Configuración Completada</h4>";
    echo "<p>El sistema ahora está configurado para usar el driver nativo USB <code>hikvision_usb</code>.</p>";
    echo "</div>";
    
    // 3. Test de captura
    echo "<div class='alert alert-warning mt-4'>";
    echo "<h4>🧪 Prueba de Captura:</h4>";
    echo "<p>Asegúrate de que el sensor esté conectado y prueba la captura:</p>";
    // Link directo con driver=hikvision_usb
    echo "<a href='http://localhost:8888/capture?driver=hikvision_usb' target='_blank' class='btn btn-lg btn-primary'>Iniciar Test de Captura</a>";
    echo "</div>";
    
    echo "<div class='alert alert-dark mt-4'>";
    echo "<h5>ℹ️ Nota técnica:</h5>";
    echo "<p>Este modo usa <code>bridge_hik_usb.exe</code> y la librería <code>FPModule_SDK_x64.dll</code> para comunicarse directamente con el puerto USB.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><h4>❌ Error:</h4>" . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "        </div>
        <div class='card-footer'>
            <a href='dashboard.php' class='btn btn-secondary'>← Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>";
?>
