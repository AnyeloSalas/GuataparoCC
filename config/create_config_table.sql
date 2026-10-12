CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT,
    descripcion VARCHAR(255),
    tipo VARCHAR(20) DEFAULT 'text', -- text, number, boolean, color, email, textarea
    categoria VARCHAR(50) DEFAULT 'general', -- general, apariencia, seguridad, notificaciones, votacion
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default values
INSERT INTO configuracion (clave, valor, descripcion, tipo, categoria) VALUES
-- GENERAL
('sistema_nombre', 'Sistema Guataparo', 'Nombre del Sistema', 'text', 'general'),
('sistema_descripcion', 'Sistema de Gestión de Socios', 'Descripción del Sistema', 'textarea', 'general'),
('sistema_email', 'contacto@guataparo.com', 'Email de Contacto', 'email', 'general'),
('sistema_telefono', '+58 123 4567890', 'Teléfono de Contacto', 'text', 'general'),
('sistema_direccion', 'Valencia, Carabobo, Venezuela', 'Dirección Física', 'text', 'general'),

-- APARIENCIA
('color_primario', '#0d6efd', 'Color Primario del Tema', 'color', 'apariencia'),
('color_secundario', '#6c757d', 'Color Secundario del Tema', 'color', 'apariencia'),
('color_exito', '#198754', 'Color de Éxito', 'color', 'apariencia'),
('color_peligro', '#dc3545', 'Color de Peligro', 'color', 'apariencia'),
('logo_url', '/assets/img/logo.png', 'URL del Logo', 'text', 'apariencia'),

-- SEGURIDAD
('sesion_timeout', '3600', 'Tiempo de Sesión (segundos)', 'number', 'seguridad'),
('intentos_login_max', '5', 'Máximo Intentos de Login', 'number', 'seguridad'),
('bloqueo_tiempo', '900', 'Tiempo de Bloqueo (segundos)', 'number', 'seguridad'),
('mantenimiento_modo', '0', 'Modo Mantenimiento', 'boolean', 'seguridad'),
('backup_automatico', '1', 'Backup Automático', 'boolean', 'seguridad'),

-- NOTIFICACIONES
('notif_email_activo', '1', 'Notificaciones por Email', 'boolean', 'notificaciones'),
('notif_email_admin', 'admin@guataparo.com', 'Email del Administrador', 'email', 'notificaciones'),
('notif_nuevos_socios', '1', 'Notificar Nuevos Socios', 'boolean', 'notificaciones'),
('notif_votaciones', '1', 'Notificar Votaciones', 'boolean', 'notificaciones'),

-- VOTACIÓN
('votacion_requiere_huella', '0', 'Requiere Huella para Votar', 'boolean', 'votacion'),
('votacion_mostrar_resultados', '1', 'Mostrar Resultados en Tiempo Real', 'boolean', 'votacion'),
('votacion_permitir_cambio', '0', 'Permitir Cambiar Voto', 'boolean', 'votacion'),
('votacion_dias_postulacion', '30', 'Días de Postulación', 'number', 'votacion')

ON DUPLICATE KEY UPDATE clave=clave;
