<?php

/**
 * Authentication Middleware
 * Ensures user is logged in before accessing protected routes
 */
class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function check() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar usuario_id (nuevo) o usuario (legacy)
        return (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) || 
               (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']));
    }
    
    /**
     * Require authentication, redirect to login if not authenticated
     * @param string $redirectUrl URL to redirect if not authenticated
     */
    public static function require($redirectUrl = 'index.php?action=login') {
        if (!self::check()) {
            header("Location: $redirectUrl");
            exit();
        }
    }
    
    /**
     * Check if user has admin role
     * @return bool
     */
    public static function isAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $tipo = strtolower($_SESSION['tipousuario'] ?? ($_SESSION['rol'] ?? ''));
        return $tipo === 'administrador';
    }
    
    /**
     * Require admin role
     * @param string $redirectUrl URL to redirect if not admin
     */
    public static function requireAdmin($redirectUrl = 'index.php?action=dashboard') {
        self::require();
        
        if (!self::isAdmin()) {
            header("Location: $redirectUrl");
            exit();
        }
    }
    
    /**
     * Get current user ID
     * @return int|null
     */
    public static function userId() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['usuario_id'] ?? null;
    }
    
    /**
     * Get current user role
     * @return string|null
     */
    public static function userRole() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['tipousuario'] ?? ($_SESSION['rol'] ?? null);
    }
}
?>
