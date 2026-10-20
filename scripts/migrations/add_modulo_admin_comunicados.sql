-- Migración incremental: módulo 24 - Administración de Comunicados
-- Fecha: 2026-07-28

-- 1. Registrar el nuevo módulo administrativo
INSERT INTO t_modulos (id_modulo, nombre_modulo, usr_insert, fec_insert)
VALUES (24, 'Administración de Comunicados', 'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_modulo) DO UPDATE SET nombre_modulo = EXCLUDED.nombre_modulo,
                                      fec_delete    = NULL,
                                      usr_delete    = NULL;

-- 2. Registrar operaciones (id_modulo * 10 + secuencia)
INSERT INTO t_operaciones (id_operacion, id_modulo, nombre_operacion, usr_insert, fec_insert)
VALUES
    (241, 24, 'VER',        'seed', CURRENT_TIMESTAMP),
    (242, 24, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (243, 24, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (244, 24, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),
    (245, 24, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_operacion) DO UPDATE SET id_modulo        = EXCLUDED.id_modulo,
                                          nombre_operacion = EXCLUDED.nombre_operacion,
                                          fec_delete       = NULL,
                                          usr_delete       = NULL;

-- 3. Asignar todas las operaciones del módulo 24 al rol ADMINISTRADOR (id_rol = 1)
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
SELECT 1, id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE id_modulo = 24
  AND fec_delete IS NULL
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;
