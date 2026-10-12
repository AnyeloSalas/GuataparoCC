<?php
// Script para inicializar la base de datos del módulo de IA
// Asegurarnos de cargar la configuración correcta
define('PROJECT_ROOT', __DIR__);
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Conectado a la base de datos...<br>";

    $sql = "CREATE TABLE IF NOT EXISTS ia_conocimiento (
        id INT AUTO_INCREMENT PRIMARY KEY,
        palabras_clave VARCHAR(255) NOT NULL,
        respuesta TEXT NOT NULL,
        categoria VARCHAR(50) DEFAULT 'General',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "✅ Tabla 'ia_conocimiento' creada exitosamente (o ya existía).<br>";
    echo "Ahora puedes usar el módulo de Entrenamiento de IA.";

} catch(PDOException $e) {
    echo "❌ Error al crear tabla: " . $e->getMessage();
    // Intentar ver qué tablas existen
    try {
        $stmt = $db->query("SHOW TABLES");
        echo "<br>Tablas existentes:<br>";
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo $row[0] . "<br>";
        }
    } catch (Exception $ex) {
        echo "<br>No se pudieron listar las tablas.";
    }
}
?>
