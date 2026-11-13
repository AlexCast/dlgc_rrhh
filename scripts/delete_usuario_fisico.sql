-- =====================================================================
-- delete_usuario_fisico.sql
-- Borrado FÍSICO (permanente) de un usuario de t_usuarios.
--
-- Pensado para limpiar usuarios de PRUEBA en producción que no tienen
-- datos de negocio (sin fila en t_empleados, sin solicitudes, etc.).
--
-- USO:
--   1) Edita la línea "SET @uid" con el id_usuario (documento).
--   2) Ejecuta desde consola:
--        mariadb -u TU_USUARIO -p NOMBRE_BD < "c:\Apache24\htdocs\dlgc_rrhh\scripts\delete_usuario_fisico.sql"
--      o desde el cliente interactivo:
--        source c:/Apache24/htdocs/dlgc_rrhh/scripts/delete_usuario_fisico.sql
--
-- QUÉ PASA CON LOS TRIGGERS (respuesta corta: nada queda alterado):
--   t_usuarios tiene un trigger de seguridad, tri_soft_delete_usuarios,
--   que BLOQUEA el DELETE físico con un SIGNAL. Este script:
--     1. Lo elimina (DROP) justo antes del DELETE,
--     2. Ejecuta el DELETE,
--     3. Lo vuelve a crear IDÉNTICO inmediatamente después.
--   Es decir: el trigger solo está "ausente" durante milisegundos,
--   dentro de esta misma ejecución. Al final el script imprime
--   SHOW TRIGGERS para que lo verifiques con tus propios ojos.
--
-- SI ALGO SE INTERRUMPE A MITAD DE CAMINO:
--   Vuelve a ejecutar scripts/triggers/softdelete.sql (usa CREATE OR
--   REPLACE para cada trigger) y todos los triggers de soft delete
--   quedan restaurados exactamente como estaban.
--
-- ANTES DE CONTINUAR: revisa la salida del paso 2. Si alguna tabla de
-- las que tienen trigger de soft delete muestra filas > 0, ABORTA:
-- para esos casos usa el borrado lógico normal de la app
-- (Usuarios > Eliminar) o limpia primero por la aplicación.
-- =====================================================================

SET @uid = '91426569';  -- <-- EDITAR: número de documento (id_usuario)

-- ---------------------------------------------------------------------
-- PASO 1: ¿Existe el usuario? (verificación visual antes de tocar nada)
-- ---------------------------------------------------------------------
SELECT id_usuario, username, correo, usr_insert, fec_insert, fec_delete
FROM t_usuarios
WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 2: Tablas con trigger de soft delete (ESTE SCRIPT NO LAS TOCA).
--         Si sale alguna fila > 0, NO sigas ejecutando el resto.
-- ---------------------------------------------------------------------
SELECT 't_empleados' AS tabla, COUNT(*) AS filas
FROM t_empleados WHERE id_usuario = @uid
UNION ALL
SELECT 't_usuarios_operaciones', COUNT(*)
FROM t_usuarios_operaciones WHERE id_usuario = @uid
UNION ALL
SELECT 't_solicitudes_permisos (id_jefe_responsable)', COUNT(*)
FROM t_solicitudes_permisos WHERE id_jefe_responsable = @uid
UNION ALL
SELECT 't_documentos_versiones (id_aprobador)', COUNT(*)
FROM t_documentos_versiones WHERE id_aprobador = @uid;

-- ---------------------------------------------------------------------
-- PASO 3: Limpieza de tablas hijas SIN trigger de soft delete.
--         Son registros satélite (tokens, intentos, vistos, etc.) que
--         sí permiten DELETE físico.
-- ---------------------------------------------------------------------
DELETE FROM t_remember_tokens      WHERE id_usuario = @uid;
DELETE FROM t_recuperacion_contrasena WHERE id_usuario = @uid;
DELETE FROM t_intentos_correo      WHERE id_usuario = @uid;
DELETE FROM t_intentos_subida      WHERE id_usuario = @uid;
DELETE FROM t_comunicados_vistos   WHERE id_usuario = @uid;
DELETE FROM t_permisos_aprobaciones WHERE id_aprobador = @uid;
DELETE FROM t_sst_buzon_quejas
WHERE id_usuario = @uid OR id_encargado = @uid OR cancelado_por = @uid;
-- Si el usuario aún no verificó su correo, vive en t_registros_pendientes
-- (la verificación se elimina en cascada):
DELETE FROM t_registros_pendientes WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 4: Respaldo de la fila antes de borrarla (prudencia en producción).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bck_usuarios_pruebas LIKE t_usuarios;
INSERT INTO bck_usuarios_pruebas
SELECT * FROM t_usuarios WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 5: Borrado físico.
--   DROP del trigger de guarda -> DELETE -> re-creación IDÉNTICA.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tri_soft_delete_usuarios;

DELETE FROM t_usuarios WHERE id_usuario = @uid;

DELIMITER //
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios
BEFORE DELETE ON t_usuarios
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_usuarios; utilice el borrado lógico.';
END//
DELIMITER ;

-- ---------------------------------------------------------------------
-- PASO 6: Verificación final.
--   a) El trigger DEBE aparecer listado (queda restaurado).
--   b) Deben quedar 0 filas en t_usuarios para ese id.
-- ---------------------------------------------------------------------
SHOW TRIGGERS LIKE 't_usuarios';

SELECT COUNT(*) AS quedan_en_t_usuarios
FROM t_usuarios
WHERE id_usuario = @uid;
