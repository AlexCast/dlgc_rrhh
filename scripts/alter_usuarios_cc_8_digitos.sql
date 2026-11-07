-- =============================================================================
-- ALTER para ampliar el mínimo de la CC (tipo_documento = 'CC') de 10 a 8 dígitos.
-- Permite cédulas de ciudadanía entre 8 y 10 dígitos en t_usuarios.
--
-- Requisitos:
--   * MariaDB >= 10.2.22 (soporta DROP CONSTRAINT para CHECK).
--   * El nombre del constraint en producción debe ser chk_t_usuarios_documento_formato.
--     Verificar antes con:
--       SELECT CONSTRAINT_NAME
--       FROM information_schema.TABLE_CONSTRAINTS
--       WHERE TABLE_SCHEMA = DATABASE()
--         AND TABLE_NAME = 't_usuarios'
--         AND CONSTRAINT_TYPE = 'CHECK';
--
-- Nota: los registros CC existentes de 10 dígitos siguen cumpliendo {8,10},
-- por lo que no se requiere migración de datos.
-- =============================================================================

ALTER TABLE t_usuarios
    DROP CONSTRAINT chk_t_usuarios_documento_formato;

ALTER TABLE t_usuarios
    ADD CONSTRAINT chk_t_usuarios_documento_formato
        CHECK (
            (tipo_documento = 'CC' AND id_usuario REGEXP '^[0-9]{8,10}$') OR
            (tipo_documento = 'PPT' AND id_usuario REGEXP '^[A-Z0-9\-]{5,20}$') OR
            (tipo_documento = 'CE' AND id_usuario REGEXP '^[A-Z0-9]{5,20}$')
        );
