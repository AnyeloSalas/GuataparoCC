<?php

class Csrf {
    
    /**
     * Generate a CSRF token and store it in the session.
     * @return string The generated token
     */
    public static function getToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the CSRF token from the request.
     * @param string $token The token to verify
     * @return bool True if valid, false otherwise
     */
    public static function verify($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate a hidden input field with the CSRF token.
     * @return string HTML hidden input element
     */
    public static function tokenField() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken()) . '">';
    }
    
    /**
     * Validate the CSRF token from $_POST request.
     * @return bool True if valid, false otherwise
     */
    public static function validateRequest() {
        $token = $_POST['csrf_token'] ?? '';
        return self::verify($token);
    }
}
?>
