<?php
/**
 * Script para migrar huellas dactilares guardadas en texto plano (Base64) 
 * a texto encriptado usando AES-256-CBC.
 * 
 * Uso: php scripts/encrypt_existing_huellas.php
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Models/CryptoHelper.php';

echo "Iniciando proceso de encriptación de huellas (Biometric Tokenization)...\n";

$database = new Database();
$db = $database->getConnection();

// 1. Procesar tabla 'usuarios'
$query = "SELECT usuario, huella_template FROM usuarios WHERE huella_template IS NOT NULL AND huella_template != ''";
$stmt = $db->query($query);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatedUsers = 0;
foreach ($usuarios as $row) {
    $template = $row['huella_template'];
    // Para saber si ya está encriptado, tratamos de desencriptarlo en modo estricto.
    $testDecrypt = CryptoHelper::decrypt($template, true);
    
    if ($testDecrypt === false) {
        // Asumimos que no está encriptado (es un template antiguo)
        $encryptedTemplate = CryptoHelper::encrypt($template);
        
        $updateStmt = $db->prepare("UPDATE usuarios SET huella_template = :template WHERE usuario = :usuario");
        $updateStmt->bindParam(':template', $encryptedTemplate);
        $updateStmt->bindParam(':usuario', $row['usuario']);
        if ($updateStmt->execute()) {
            $updatedUsers++;
            echo "Usuario {$row['usuario']}: Huella encriptada exitosamente.\n";
        }
    } else {
        echo "Usuario {$row['usuario']}: Huella YA estaba encriptada.\n";
    }
}

// 2. Procesar tabla 'personas' (socios)
$query = "SELECT id, cedula, huella_template FROM personas WHERE huella_template IS NOT NULL AND huella_template != ''";
$stmt = $db->query($query);
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatedPersonas = 0;
foreach ($personas as $row) {
    $template = $row['huella_template'];
    $testDecrypt = CryptoHelper::decrypt($template, true);
    
    if ($testDecrypt === false) {
        $encryptedTemplate = CryptoHelper::encrypt($template);
        
        $updateStmt = $db->prepare("UPDATE personas SET huella_template = :template WHERE id = :id");
        $updateStmt->bindParam(':template', $encryptedTemplate);
        $updateStmt->bindParam(':id', $row['id']);
        if ($updateStmt->execute()) {
            $updatedPersonas++;
            echo "Socio {$row['cedula']}: Huella encriptada exitosamente.\n";
        }
    } else {
        echo "Socio {$row['cedula']}: Huella YA estaba encriptada.\n";
    }
}

echo "--------------------------------------------------------\n";
echo "Proceso finalizado.\n";
echo "Usuarios actualizados: $updatedUsers\n";
echo "Socios actualizados: $updatedPersonas\n";
?>
