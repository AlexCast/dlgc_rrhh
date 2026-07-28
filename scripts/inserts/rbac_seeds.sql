-- ============================================================
-- Seeds RBAC para DLGC_RRHH
-- ============================================================
-- Tablas a poblar: t_roles, t_modulos, t_operaciones, t_roles_operaciones,
-- t_tipos_permisos.
--
-- Convención de IDs:
--   Módulos: 1=Inicio, 3=Solicitudes y Permisos, 4=Empleados (CRUD SRC), 5=Comunicados,
--            6..21 = módulos de administración (SRC),
--            22 = Directorio de Empleados (templates/empleados.php + ficha_tecnica.php),
--            23 = Códigos de Registro.
--   Operaciones por módulo: id_modulo*10 + [1=VER, 2=CREAR, 3=ACTUALIZAR, 4=ELIMINAR, 5=RESTAURAR].
--   Roles: 1=ADMINISTRADOR, 2=EMPLEADO.
--
-- Ejecutar después de crear el esquema (modelo_db.sql) y antes de usar la app.
-- ============================================================

-- --------------------------------------------------------
-- 1. Roles
-- --------------------------------------------------------
INSERT INTO t_roles (id_rol, nombre_rol, usr_insert, fec_insert)
VALUES
    (1, 'ADMINISTRADOR', 'seed', CURRENT_TIMESTAMP),
    (2, 'EMPLEADO',      'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_rol) DO UPDATE SET nombre_rol = EXCLUDED.nombre_rol,
                                     fec_delete = NULL,
                                     usr_delete = NULL;

-- --------------------------------------------------------
-- 2. Módulos
-- --------------------------------------------------------
INSERT INTO t_modulos (id_modulo, nombre_modulo, usr_insert, fec_insert)
VALUES
    (1,  'Inicio',                   'seed', CURRENT_TIMESTAMP),
    (3,  'Solicitudes y Permisos',   'seed', CURRENT_TIMESTAMP),
    (4,  'Empleados',                'seed', CURRENT_TIMESTAMP),
    (5,  'Comunicados',              'seed', CURRENT_TIMESTAMP),
    (22, 'Directorio de Empleados',  'seed', CURRENT_TIMESTAMP),
    (6,  'Roles Operaciones',        'seed', CURRENT_TIMESTAMP),
    (7,  'Afiliaciones Empleados',   'seed', CURRENT_TIMESTAMP),
    (8,  'Áreas',                    'seed', CURRENT_TIMESTAMP),
    (9,  'ARL',                      'seed', CURRENT_TIMESTAMP),
    (10, 'Bancos',                   'seed', CURRENT_TIMESTAMP),
    (11, 'Caja Compensación',        'seed', CURRENT_TIMESTAMP),
    (12, 'Cesantías',                'seed', CURRENT_TIMESTAMP),
    (13, 'Contratos Empleados',      'seed', CURRENT_TIMESTAMP),
    (14, 'EPS',                      'seed', CURRENT_TIMESTAMP),
    (15, 'Nómina',                   'seed', CURRENT_TIMESTAMP),
    (16, 'Pensiones',                'seed', CURRENT_TIMESTAMP),
    (17, 'Roles',                    'seed', CURRENT_TIMESTAMP),
    (18, 'Módulos',                  'seed', CURRENT_TIMESTAMP),
    (19, 'Operaciones',              'seed', CURRENT_TIMESTAMP),
    (20, 'Usuarios',                 'seed', CURRENT_TIMESTAMP),
    (21, 'Permisos por Usuario',     'seed', CURRENT_TIMESTAMP),
    (23, 'Códigos de Registro',      'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_modulo) DO UPDATE SET nombre_modulo = EXCLUDED.nombre_modulo,
                                      fec_delete    = NULL,
                                      usr_delete    = NULL;

-- --------------------------------------------------------
-- 3. Operaciones
-- --------------------------------------------------------
INSERT INTO t_operaciones (id_operacion, id_modulo, nombre_operacion, usr_insert, fec_insert)
VALUES
    -- 1. Inicio
    (11, 1, 'VER',        'seed', CURRENT_TIMESTAMP),
    (12, 1, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (13, 1, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (14, 1, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 3. Solicitudes y Permisos
    (31, 3, 'VER',        'seed', CURRENT_TIMESTAMP),
    (32, 3, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (33, 3, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (34, 3, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 4. Empleados
    (41, 4, 'VER',        'seed', CURRENT_TIMESTAMP),
    (42, 4, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (43, 4, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (44, 4, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 5. Comunicados
    (51, 5, 'VER',        'seed', CURRENT_TIMESTAMP),
    (52, 5, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (53, 5, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (54, 5, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 22. Directorio de Empleados
    (221, 22, 'VER',        'seed', CURRENT_TIMESTAMP),

    -- 6. Roles Operaciones
    (61, 6, 'VER',        'seed', CURRENT_TIMESTAMP),
    (62, 6, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (63, 6, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (64, 6, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 7. Afiliaciones Empleados
    (71, 7, 'VER',        'seed', CURRENT_TIMESTAMP),
    (72, 7, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (73, 7, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (74, 7, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 8. Áreas
    (81, 8, 'VER',        'seed', CURRENT_TIMESTAMP),
    (82, 8, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (83, 8, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (84, 8, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 9. ARL
    (91, 9, 'VER',        'seed', CURRENT_TIMESTAMP),
    (92, 9, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (93, 9, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (94, 9, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 10. Bancos
    (101, 10, 'VER',        'seed', CURRENT_TIMESTAMP),
    (102, 10, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (103, 10, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (104, 10, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 11. Caja Compensación
    (111, 11, 'VER',        'seed', CURRENT_TIMESTAMP),
    (112, 11, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (113, 11, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (114, 11, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 12. Cesantías
    (121, 12, 'VER',        'seed', CURRENT_TIMESTAMP),
    (122, 12, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (123, 12, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (124, 12, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 13. Contratos Empleados
    (131, 13, 'VER',        'seed', CURRENT_TIMESTAMP),
    (132, 13, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (133, 13, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (134, 13, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 14. EPS
    (141, 14, 'VER',        'seed', CURRENT_TIMESTAMP),
    (142, 14, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (143, 14, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (144, 14, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 15. Nómina
    (151, 15, 'VER',        'seed', CURRENT_TIMESTAMP),
    (152, 15, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (153, 15, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (154, 15, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 16. Pensiones
    (161, 16, 'VER',        'seed', CURRENT_TIMESTAMP),
    (162, 16, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (163, 16, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (164, 16, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 17. Roles
    (171, 17, 'VER',        'seed', CURRENT_TIMESTAMP),
    (172, 17, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (173, 17, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (174, 17, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 18. Módulos
    (181, 18, 'VER',        'seed', CURRENT_TIMESTAMP),
    (182, 18, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (183, 18, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (184, 18, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 19. Operaciones
    (191, 19, 'VER',        'seed', CURRENT_TIMESTAMP),
    (192, 19, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (193, 19, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (194, 19, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 20. Usuarios
    (201, 20, 'VER',        'seed', CURRENT_TIMESTAMP),
    (202, 20, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (203, 20, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (204, 20, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 21. Permisos por Usuario
    (211, 21, 'VER',        'seed', CURRENT_TIMESTAMP),
    (212, 21, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (213, 21, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (214, 21, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- 23. Códigos de Registro
    (231, 23, 'VER',        'seed', CURRENT_TIMESTAMP),
    (232, 23, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (233, 23, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (234, 23, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),

    -- RESTAURAR (id_modulo * 10 + 5)
    (15,   1, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (35,   3, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (45,   4, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (55,   5, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (65,   6, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (75,   7, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (85,   8, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (95,   9, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (105, 10, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (115, 11, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (125, 12, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (135, 13, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (145, 14, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (155, 15, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (165, 16, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (175, 17, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (185, 18, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (195, 19, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (205, 20, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (215, 21, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (235, 23, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_operacion) DO UPDATE SET id_modulo       = EXCLUDED.id_modulo,
                                          nombre_operacion = EXCLUDED.nombre_operacion,
                                          fec_delete       = NULL,
                                          usr_delete       = NULL;

-- --------------------------------------------------------
-- 4. Asignaciones Rol-Operación
-- --------------------------------------------------------
-- ADMINISTRADOR: todo
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
SELECT 1, id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE fec_delete IS NULL
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;

-- EMPLEADO: solo módulos operativos básicos
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
VALUES
    -- Inicio: ver
    (2, 11, 'seed', CURRENT_TIMESTAMP),
    -- Solicitudes y Permisos: ver + crear
    (2, 31, 'seed', CURRENT_TIMESTAMP),
    (2, 32, 'seed', CURRENT_TIMESTAMP),
    -- Directorio de Empleados: ver
    (2, 221, 'seed', CURRENT_TIMESTAMP),
    -- Comunicados: ver
    (2, 51, 'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;

-- Asegurar que ADMINISTRADOR tenga acceso al nuevo módulo 23 si se agrega de forma incremental.
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
SELECT 1, id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE id_modulo = 23
  AND fec_delete IS NULL
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;

-- --------------------------------------------------------
-- 5. Tipos de permiso (catálogo del formulario)
-- --------------------------------------------------------
INSERT INTO t_tipos_permisos (nombre_tipo, descuenta_tiempo, usr_insert, fec_insert)
SELECT nombre_tipo, descuenta_tiempo, usr_insert, fec_insert
FROM (VALUES
    ('Estudio',            FALSE, 'seed', CURRENT_TIMESTAMP),
    ('Calamidad Doméstica', FALSE, 'seed', CURRENT_TIMESTAMP),
    ('Otro Motivo',         FALSE, 'seed', CURRENT_TIMESTAMP),
    ('Consulta Médica',     FALSE, 'seed', CURRENT_TIMESTAMP),
    ('Fuerza Mayor',        FALSE, 'seed', CURRENT_TIMESTAMP)
) AS v(nombre_tipo, descuenta_tiempo, usr_insert, fec_insert)
WHERE NOT EXISTS (
    SELECT 1 FROM t_tipos_permisos t WHERE t.nombre_tipo = v.nombre_tipo
);
