<?php

class CryptoHelper {
    /**
     * Encripta un texto usando AES-256-CBC.
     * Diseñado para cifrar las plantillas biométricas (huellas) antes de guardarlas en la BD.
     */
    public static function encrypt($data) {
        if (empty($data)) return $data;
        
        // No volver a encriptar si parece que ya tiene el formato IV + Data en base64
        // (aunque es difícil estar 100% seguro, podríamos probar desencriptarlo primero)
        $testDecrypt = self::decrypt($data, true);
        if ($testDecrypt !== false && $testDecrypt !== $data) {
            // Ya estaba encriptado
            return $data;
        }
        $encryptionKey = getenv('FINGERPRINT_ENCRYPTION_KEY');
        if (!$encryptionKey) {
            die("Error Crítico: FINGERPRINT_ENCRYPTION_KEY no definida en .env");
        }
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, $iv);
        return base64_encode($iv . $encryptedData);
    }

    /**
     * Desencripta un texto cifrado con AES-256-CBC.
     * Si no se puede desencriptar (ej. era un template antiguo en texto plano base64), 
     * lo retorna intacto (fallback).
     */
    public static function decrypt($encodedData, $strict = false) {
        if (empty($encodedData)) return $encodedData;
        $encryptionKey = getenv('FINGERPRINT_ENCRYPTION_KEY');
        if (!$encryptionKey) {
            die("Error Crítico: FINGERPRINT_ENCRYPTION_KEY no definida en .env");
        }
        $decoded = base64_decode($encodedData, true);
        
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        if ($decoded !== false && strlen($decoded) > $ivLength) {
            $iv = substr($decoded, 0, $ivLength);
            $encryptedData = substr($decoded, $ivLength);
            $decryptedTemplate = openssl_decrypt($encryptedData, 'aes-256-cbc', $encryptionKey, 0, $iv);
            
            // Si la desencriptación es exitosa, lo retornamos
            if ($decryptedTemplate !== false) {
                return $decryptedTemplate;
            }
        }

        // Fallback: Si falla la desencriptación, asumimos que es un template antiguo
        return $strict ? false : $encodedData;
    }
}
?>
