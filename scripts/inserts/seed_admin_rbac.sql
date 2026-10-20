-- ============================================================
-- seed_admin_rbac.sql
-- Crea (o restaura) el usuario administrador y le asigna TODOS
-- los permisos directos en t_usuarios_operaciones.
--
-- Idempotente: se puede ejecutar cuantas veces se necesite.
-- Requisito previo: haber cargado rbac_seeds_mariadb.sql
-- (roles, módulos y operaciones) en la base de datos.
--
-- Credenciales de prueba:
--   usuario: admin
--   clave:   Admin123*
-- CAMBIAR LA CLAVE EN PRODUCCIÓN.
-- ============================================================

-- La auditoría (triggers) registrará este actor en usr_insert/usr_update.
SET @app_current_user = 'seed_admin_rbac';

-- 1. Asegurar el rol ADMINISTRADOR (id_rol = 1).
INSERT INTO t_roles (id_rol, nombre_rol, usr_insert, fec_insert)
VALUES (1, 'ADMINISTRADOR', 'seed', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
    nombre_rol = VALUES(nombre_rol),
    fec_delete = NULL,
    usr_delete = NULL;

-- 2. Crear/actualizar el usuario admin con los datos de la migración.
--    contrasena = password_hash('Admin123*', PASSWORD_DEFAULT)
INSERT INTO t_usuarios
    (id_usuario, id_rol, username, tipo_documento, primer_nombre, primer_apellido,
     correo, correo_verificado, contrasena)
VALUES
    ('1234567890', 1, 'admin', 'CC', 'Admin', 'General',
     'admin@dlgc.com', TRUE, '$2y$12$kvSsZ1T9b2dZ7dDEyARtz.3SP33/htl9b/exIGr3I1/ZJh4V1jHt.')
ON DUPLICATE KEY UPDATE
    id_rol            = VALUES(id_rol),
    username          = VALUES(username),
    primer_nombre     = VALUES(primer_nombre),
    primer_apellido   = VALUES(primer_apellido),
    correo            = VALUES(correo),
    correo_verificado = VALUES(correo_verificado),
    contrasena        = VALUES(contrasena),
    fec_delete        = NULL,
    usr_delete        = NULL;

-- 3. Asignar todos los permisos directos al admin en t_usuarios_operaciones.
INSERT INTO t_usuarios_operaciones (id_usuario, id_operacion, usr_insert, fec_insert)
SELECT '1234567890', id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE fec_delete IS NULL
ON DUPLICATE KEY UPDATE
    fec_delete = NULL,
    usr_delete = NULL;
