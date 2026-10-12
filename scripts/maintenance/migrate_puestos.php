<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Create the table
    $query = "CREATE TABLE IF NOT EXISTS puestos_trabajo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($query);
    echo "Table puestos_trabajo ensured.\n";

    // 2. Fetch existing distinct 'puestotrabajo' from usuarios
    $stmt = $conn->query("SELECT DISTINCT puestotrabajo FROM usuarios WHERE puestotrabajo IS NOT NULL AND puestotrabajo != ''");
    $existing_puestos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Insert unique ones into puestos_trabajo if they don't exist
    $insertStmt = $conn->prepare("INSERT INTO puestos_trabajo (nombre, activo) SELECT :nombre, 1 WHERE NOT EXISTS (SELECT 1 FROM puestos_trabajo WHERE nombre = :nombre2)");
    
    foreach ($existing_puestos as $puesto) {
        $puesto_clean = trim($puesto);
        if(!empty($puesto_clean)) {
             $insertStmt->execute([':nombre' => $puesto_clean, ':nombre2' => $puesto_clean]);
        }
    }
    echo "Migration of existing puestos completed.\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
