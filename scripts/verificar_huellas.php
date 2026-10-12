<?php
// Script de verificación de huellas en base de datos

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== VERIFICACIÓN DE HUELLAS EN BASE DE DATOS ===\n\n";

// 1. Verificar usuarios
echo "1. USUARIOS:\n";
echo str_repeat("-", 80) . "\n";

$query = "SELECT usuario, cedula, 
          CHAR_LENGTH(huella_template) as longitud_huella,
          IF(huella_template IS NULL OR huella_template = '', 'NO', 'SI') as tiene_huella,
          SUBSTRING(huella_template, 1, 50) as muestra_huella
          FROM usuarios 
          ORDER BY usuario";

$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {
    printf("Usuario: %-20s | Cédula: %-15s | Huella: %-3s | Longitud: %6s bytes\n",
        $usuario['usuario'],
        $usuario['cedula'],
        $usuario['tiene_huella'],
        $usuario['longitud_huella'] ?? '0'
    );
    if ($usuario['tiene_huella'] === 'SI' && $usuario['longitud_huella'] < 100) {
        echo "  ⚠️ WARNING: Template muy corto (probable error)\n";
    }
}

echo "\n";

// 2. Verificar socios (personas)
echo "2. SOCIOS (PERSONAS):\n";
echo str_repeat("-", 80) . "\n";

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
        printf("Socio: %-30s | Cédula: %-15s | Huella: %-3s | Longitud: %6s bytes\n",
            substr($socio['nombre'], 0, 30),
            $socio['cedula'],
            $socio['tiene_huella'],
            $socio['longitud_huella'] ?? '0'
        );
        if ($socio['tiene_huella'] === 'SI' && $socio['longitud_huella'] < 100) {
            echo "  ⚠️ WARNING: Template muy corto (probable error)\n";
        }
    }
} catch (Exception $e) {
    echo "Error al consultar socios: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Estadísticas generales
echo "3. ESTADÍSTICAS:\n";
echo str_repeat("-", 80) . "\n";

$stats = $db->query("SELECT 
    COUNT(*) as total,
    SUM(IF(huella_template IS NOT NULL AND huella_template != '', 1, 0)) as con_huella,
    AVG(CHAR_LENGTH(huella_template)) as longitud_promedio
    FROM usuarios")->fetch(PDO::FETCH_ASSOC);

printf("Total usuarios: %d\n", $stats['total']);
printf("Con huella registrada: %d\n", $stats['con_huella']);
printf("Longitud promedio de template: %.2f bytes\n", $stats['longitud_promedio'] ?? 0);

echo "\n";

// 4. Verificar últimos registros
echo "4. ÚLTIMAS 5 HUELLAS REGISTRADAS (desde auditoría):\n";
echo str_repeat("-", 80) . "\n";

try {
    $auditQuery = "SELECT * FROM auditoria_huellas 
                   WHERE tipo_evento IN ('registro', 'actualizacion')
                   ORDER BY fecha_hora DESC 
                   LIMIT 5";
    $auditStmt = $db->prepare($auditQuery);
    $auditStmt->execute();
    $audits = $auditStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($audits as $audit) {
        printf("%s | %s | Usuario: %s | Cédula: %s | %s\n",
            $audit['fecha_hora'],
            $audit['tipo_evento'],
            $audit['nombre_usuario'] ?? 'N/A',
            $audit['cedula_usuario'] ?? 'N/A',
            $audit['mensaje']
        );
    }
} catch (Exception $e) {
    echo "Tabla de auditoría no disponible\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>
