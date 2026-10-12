<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "<h1>Arreglando Base de Datos - Votación</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 1. Verificar si la columna existe
    $check = $db->query("SHOW COLUMNS FROM `votacion` LIKE 'es_presencial'");
    if ($check->rowCount() == 0) {
        echo "<p>Añadiendo columna 'es_presencial' a la tabla 'votacion'...</p>";
        $db->exec("ALTER TABLE `votacion` ADD COLUMN `es_presencial` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 si fue en taquilla con huella, 0 si fue remoto via token'");
        echo "<p style='color: green;'>Columna añadida con éxito.</p>";
    } else {
        echo "<p style='color: orange;'>La columna 'es_presencial' ya existe.</p>";
    }
    
    // 2. Crear tabla de tokens si no existe
    echo "<p>Verificando tabla 'voto_remoto_tokens'...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS `voto_remoto_tokens` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `accion` varchar(50) NOT NULL,
      `token_hash` varchar(255) NOT NULL,
      `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `expiracion` datetime NOT NULL,
      `usado` tinyint(1) NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `idx_token_hash` (`token_hash`),
      KEY `idx_accion` (`accion`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $db->exec($sql);
    echo "<p style='color: green;'>Tabla de tokens verificada/creada con éxito.</p>";
    
    echo "<h2>Proceso Finalizado. Ya puedes volver a probar la votación.</h2>";
    echo "<a href='index.php'>Volver al Inicio</a>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
