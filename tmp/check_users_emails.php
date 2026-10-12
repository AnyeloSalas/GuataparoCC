<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT usuario, email FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios en la base de datos:\n";
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
