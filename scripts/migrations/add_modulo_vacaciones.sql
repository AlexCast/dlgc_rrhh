-- ============================================================
-- Migración incremental: Módulo "Gestión de Vacaciones" (id_modulo 29).
-- Se separa de la Bandeja de RRHH (módulo 27) porque es un módulo completo
-- por sí mismo (consulta de saldo, ajustes manuales, reportes), con su
-- propia entrada en el sidebar en vez de vivir como pestaña de otro módulo.
-- Aplicar sobre una base de datos VIVA. Ejecutar como un solo bloque.
-- ============================================================

INSERT INTO t_modulos (id_modulo, nombre_modulo, usr_insert, fec_insert)
VALUES (29, 'Gestión de Vacaciones', 'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_modulo) DO UPDATE SET nombre_modulo = EXCLUDED.nombre_modulo,
                                       fec_delete    = NULL,
                                       usr_delete    = NULL;

-- 291=VER (consultar saldo/reportes), 292=CREAR (registrar ajuste manual),
-- 293=ACTUALIZAR (reservado, sin uso hoy: los ajustes son append-only),
-- 294=ELIMINAR (anular un ajuste), 295=RESTAURAR (revertir una anulación).
INSERT INTO t_operaciones (id_operacion, id_modulo, nombre_operacion, usr_insert, fec_insert)
VALUES
    (291, 29, 'VER',        'seed', CURRENT_TIMESTAMP),
    (292, 29, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (293, 29, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (294, 29, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),
    (295, 29, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_operacion) DO UPDATE SET id_modulo        = EXCLUDED.id_modulo,
                                          nombre_operacion = EXCLUDED.nombre_operacion,
                                          fec_delete       = NULL,
                                          usr_delete       = NULL;

-- ADMINISTRADOR: todo (incluye el módulo 29 recién creado).
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
SELECT 1, id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE id_modulo = 29
  AND fec_delete IS NULL
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;

-- EMPLEADO: sin acceso por defecto (igual que el módulo 27, RRHH lo asigna
-- por usuario individual desde "Permisos por Usuario", módulo 21).
