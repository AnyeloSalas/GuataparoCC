<?php

class Validator {
    
    private static $errors = [];
    
    /**
     * Clean and sanitize input data
     */
    public static function clean($data) {
        if (is_array($data)) {
            return array_map([self::class, 'clean'], $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate required fields
     */
    public static function validateRequired($fields, $data) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                self::addError($field, "El campo {$field} es requerido");
                return false;
            }
        }
        return true;
    }

    /**
     * Validate password strength
     * @param string $password
     * @param int $minLength Minimum length (default: 8)
     * @param bool $requireSpecial Require special characters
     */
    public static function validatePasswordStrength($password, $minLength = 8, $requireSpecial = false) {
        if (strlen($password) < $minLength) {
            self::addError('password', "La contraseña debe tener al menos {$minLength} caracteres");
            return false;
        }
        
        if ($requireSpecial) {
            if (!preg_match('/[A-Z]/', $password)) {
                self::addError('password', 'La contraseña debe contener al menos una mayúscula');
                return false;
            }
            if (!preg_match('/[a-z]/', $password)) {
                self::addError('password', 'La contraseña debe contener al menos una minúscula');
                return false;
            }
            if (!preg_match('/[0-9]/', $password)) {
                self::addError('password', 'La contraseña debe contener al menos un número');
                return false;
            }
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                self::addError('password', 'La contraseña debe contener al menos un carácter especial');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate string length
     */
    public static function validateLength($value, $min = 0, $max = PHP_INT_MAX, $fieldName = 'campo') {
        $length = strlen($value);
        if ($length < $min || $length > $max) {
            self::addError($fieldName, "El {$fieldName} debe tener entre {$min} y {$max} caracteres");
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     */
    public static function validateNumeric($value, $fieldName = 'campo') {
        if (!is_numeric($value)) {
            self::addError($fieldName, "El {$fieldName} debe ser un valor numérico");
            return false;
        }
        return true;
    }

    /**
     * Validate integer value
     */
    public static function validateInteger($value, $fieldName = 'campo') {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            self::addError($fieldName, "El {$fieldName} debe ser un número entero");
            return false;
        }
        return true;
    }

    /**
     * Validate date format
     */
    public static function validateDate($date, $format = 'Y-m-d', $fieldName = 'fecha') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            self::addError($fieldName, "El formato de {$fieldName} no es válido");
            return false;
        }
        return true;
    }

    /**
     * Validate phone number (Venezuelan format)
     */
    public static function validatePhone($phone, $fieldName = 'teléfono') {
        // Remove spaces, dashes, parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Venezuelan phone: 11 digits (0414-1234567) or 10 digits (0212-1234567)
        if (!preg_match('/^0(4(1[24]|2[46]))\d{7}$/', $cleaned) && 
            !preg_match('/^0\d{3}\d{7}$/', $cleaned)) {
            self::addError($fieldName, "El formato de {$fieldName} no es válido");
            return false;
        }
        return true;
    }

    /**
     * Validate Venezuelan ID (Cédula) - accepts V-12345678 or just numbers 12345678
     */
    public static function validateVenezuelanId($cedula, $fieldName = 'cédula') {
        // Accept V-12345678, E-12345678, P-12345678 or just numbers (5 to 9 digits)
        $soloNumeros = preg_replace('/[^0-9]/', '', $cedula);
        if (!preg_match('/^\d{5,9}$/', $soloNumeros)) {
            self::addError($fieldName, "El formato de {$fieldName} no es válido. Ingrese entre 5 y 9 dígitos.");
            return false;
        }
        return true;
    }

    /**
     * Format a cedula to the standard V-12345678 format
     */
    public static function formatCedula($cedula, $nacionalidad = 'V') {
        if (empty($cedula)) return $cedula;
        
        // 1. Extraer solo números
        $numbers = preg_replace('/[^0-9]/', '', $cedula);
        
        // 2. Determinar prefijo
        $prefix = strtoupper(trim($nacionalidad));
        if (empty($prefix) || !in_array($prefix, ['V', 'E', 'P'])) {
            // Intentar extraer de la cedula original si tiene letra
            if (preg_match('/^([VEP])[-.]?/i', $cedula, $matches)) {
                $prefix = strtoupper($matches[1]);
            } else {
                $prefix = 'V'; // Default
            }
        }
        
        return $prefix . '-' . $numbers;
    }

    /**
     * Add validation error
     */
    private static function addError($field, $message) {
        self::$errors[$field] = $message;
    }

    /**
     * Get all validation errors
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Get error for specific field
     */
    public static function getError($field) {
        return self::$errors[$field] ?? null;
    }

    /**
     * Check if there are any errors
     */
    public static function hasErrors() {
        return !empty(self::$errors);
    }

    /**
     * Clear all errors
     */
    public static function clearErrors() {
        self::$errors = [];
    }

    /**
     * Validate multiple rules at once
     * @param array $rules Format: ['field' => ['rule1', 'rule2'], ...]
     * @param array $data The data to validate
     */
    public static function validate($rules, $data) {
        self::clearErrors();
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';
            
            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    self::addError($field, "El campo {$field} es requerido");
                    break;
                }
                
                if ($rule === 'email' && !empty($value) && !self::validateEmail($value)) {
                    self::addError($field, "El formato de email no es válido");
                    break;
                }
                
                if (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (strlen($value) < $min) {
                        self::addError($field, "El campo {$field} debe tener al menos {$min} caracteres");
                        break;
                    }
                }
                
                if ($rule === 'venezuelan_id' && !empty($value) && !self::validateVenezuelanId($value, $field)) {
                    // Error already added by validateVenezuelanId
                    break;
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (strlen($value) > $max) {
                        self::addError($field, "El campo {$field} no debe exceder {$max} caracteres");
                        break;
                    }
                }
            }
        }
        
        return !self::hasErrors();
    }
}
?>
