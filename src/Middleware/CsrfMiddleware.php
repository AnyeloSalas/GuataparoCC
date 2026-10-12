<?php

/**
 * CSRF Middleware
 * Protects forms and sensitive operations from Cross-Site Request Forgery attacks
 */
class CsrfMiddleware {
    
    /**
     * Verify CSRF token from POST request
     * @throws Exception if token is invalid
     */
    public static function verify() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (!Csrf::verify($token)) {
                http_response_code(403);
                throw new Exception('Token CSRF inválido. Por favor, recarga la página e intenta nuevamente.');
            }
        }
    }
    
    /**
     * Check if CSRF protection is enabled
     */
    public static function isEnabled() {
        return getenv('CSRF_ENABLED') !== 'false';
    }
    
    /**
     * Verify CSRF token only if enabled
     */
    public static function verifyIfEnabled() {
        if (self::isEnabled()) {
            self::verify();
        }
    }
}
?>
