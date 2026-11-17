USE Proyecto1AllanG;

-- Roles
INSERT IGNORE INTO roles (nombre) VALUES
    ('admin'),
    ('operador');

-- Configuración del sistema (regla 8: anticipación máxima para reservas, en días)
INSERT IGNORE INTO configuracion (clave, valor) VALUES
    ('anticipacion_max_dias', '30');

-- Usuarios de prueba
-- admin / admin
-- operador / operador
-- Las contraseñas están guardadas como hash bcrypt generado con password_hash() de PHP.
INSERT IGNORE INTO usuarios (rol_id, nombre, correo, password_hash) VALUES
    ((SELECT id FROM roles WHERE nombre = 'admin'), 'Administrador', 'admin@lab.local', '$2y$10$34KpP5NPR7/iLoz49EO/xe9cBc4jUoLCGSmtIX.6WZ/GJ3FUxx.ai'),
    ((SELECT id FROM roles WHERE nombre = 'operador'), 'Operador de inventario', 'operador@lab.local', '$2y$10$UlYtVpwJQP6vWS73u.63luXSXHsaRSm7UtvohKoMMIi/qewa6Cjvu');

-- Categorías de equipos
INSERT IGNORE INTO categorias (nombre, dias_max_prestamo) VALUES
    ('Computadoras portátiles', 3),
    ('Cámaras', 2),
    ('Proyectores', 1);

-- Equipos de prueba
INSERT IGNORE INTO equipos (categoria_id, codigo, nombre) VALUES
    ((SELECT id FROM categorias WHERE nombre = 'Computadoras portátiles'), 'LAB-PC-001', 'Laptop Dell Latitude 5420'),
    ((SELECT id FROM categorias WHERE nombre = 'Computadoras portátiles'), 'LAB-PC-002', 'Laptop Dell Latitude 5420'),
    ((SELECT id FROM categorias WHERE nombre = 'Cámaras'), 'LAB-CAM-001', 'Cámara Canon EOS Rebel T7'),
    ((SELECT id FROM categorias WHERE nombre = 'Proyectores'), 'LAB-PROY-001', 'Proyector Epson PowerLite X41');
