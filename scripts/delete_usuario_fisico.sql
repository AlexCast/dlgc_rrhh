-- =====================================================================
-- delete_usuario_fisico.sql
-- Borrado FÍSICO (permanente) de un usuario de t_usuarios, con o sin
-- ficha de empleado (t_empleados) y toda su cadena de datos.
--
-- Pensado para limpiar usuarios/empleados de PRUEBA en producción.
-- Si el usuario tiene ficha en t_empleados, también se purga:
-- contratos, nómina, afiliaciones, solicitudes de permiso (con motivos,
-- días aprobados, evidencias y aprobaciones), días festivos excepciones,
-- ajustes de vacaciones, documentos RRHH (con versiones), quejas SST
-- propias, y todos los registros satélite (tokens, intentos, etc.).
--
-- USO:
--   1) Edita la línea "SET @uid" con el id_usuario (documento).
--   2) Ejecuta desde consola:
--        mariadb -u TU_USUARIO -p NOMBRE_BD < "c:\Apache24\htdocs\dlgc_rrhh\scripts\delete_usuario_fisico.sql"
--      o desde el cliente interactivo:
--        source c:/Apache24/htdocs/dlgc_rrhh/scripts/delete_usuario_fisico.sql
--
-- QUÉ PASA CON LOS TRIGGERS (respuesta corta: nada queda alterado):
--   Varias de estas tablas tienen triggers de seguridad que bloquean el
--   DELETE físico con SIGNAL. Este script:
--     1. Los elimina (DROP) justo antes del borrado,
--     2. Ejecuta los DELETE en orden (hijos antes que padres),
--     3. Los vuelve a crear IDÉNTICOS inmediatamente después.
--   Los triggers solo están "ausentes" durante milisegundos, dentro de
--   esta misma ejecución. Al final, SHOW TRIGGERS lo confirma.
--
-- SI ALGO SE INTERRUMPE A MITAD DE CAMINO:
--   Vuelve a ejecutar scripts/triggers/softdelete.sql (usa CREATE OR
--   REPLACE para cada trigger) y todos los triggers de soft delete
--   quedan restaurados exactamente como estaban.
--
-- PASO 2b (referencias como actor secundario): DEBEN salir en 0.
--   Si hay filas > 0 significa que este usuario es jefe/aprobador/
--   encargado de datos de OTRAS personas. En ese caso DETENTE y no
--   sigas: purgar este usuario destruiría datos ajenos.
--
-- ARCHIVOS EN DISCO: los archivos de documentación quedan huérfanos en
--   uploads/documentos/<id_documento>/ (ver lista del PASO 1). Bórralos
--   a mano después, si aplica.
-- =====================================================================

SET @uid = '1234567891';  -- <-- EDITAR: número de documento (id_usuario)

-- ---------------------------------------------------------------------
-- PASO 1: Qué se va a borrar (verificación visual antes de tocar nada)
-- ---------------------------------------------------------------------
SELECT id_usuario, username, correo, usr_insert, fec_insert, fec_delete
FROM t_usuarios
WHERE id_usuario = @uid;

SELECT id_usuario, id_jefe, foto_perfil
FROM t_empleados
WHERE id_usuario = @uid;

-- Documentos del empleado: anota los id_documento para borrar sus
-- carpetas en disco (uploads/documentos/<id_documento>/) después.
SELECT id_documento, titulo, id_contrato
FROM t_documentos_rrhh
WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 2a: Datos propios del usuario (se van a purgar).
--          Información: aquí se espera ver los conteos reales.
-- ---------------------------------------------------------------------
SELECT 't_empleados' AS tabla, COUNT(*) AS filas
FROM t_empleados WHERE id_usuario = @uid
UNION ALL
SELECT 't_contratos_empleados', COUNT(*)
FROM t_contratos_empleados WHERE id_usuario = @uid
UNION ALL
SELECT 't_nomina', COUNT(*)
FROM t_nomina WHERE id_usuario = @uid
UNION ALL
SELECT 't_afiliaciones_empleados', COUNT(*)
FROM t_afiliaciones_empleados WHERE id_usuario = @uid
UNION ALL
SELECT 't_dias_festivos_excepciones', COUNT(*)
FROM t_dias_festivos_excepciones WHERE id_empleado = @uid
UNION ALL
SELECT 't_solicitudes_permisos', COUNT(*)
FROM t_solicitudes_permisos WHERE id_empleado = @uid
UNION ALL
SELECT 't_vacaciones_ajustes', COUNT(*)
FROM t_vacaciones_ajustes WHERE id_empleado = @uid
UNION ALL
SELECT 't_documentos_rrhh', COUNT(*)
FROM t_documentos_rrhh WHERE id_usuario = @uid
UNION ALL
SELECT 't_usuarios_operaciones', COUNT(*)
FROM t_usuarios_operaciones WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 2b: Referencias como actor SECUNDARIO (datos de OTRAS personas).
--          DEBEN salir en 0. Si alguna sale > 0, DETENTE: no sigas.
-- ---------------------------------------------------------------------
SELECT 't_empleados.id_jefe (otros a su cargo)' AS referencia, COUNT(*) AS filas
FROM t_empleados WHERE id_jefe = @uid
UNION ALL
SELECT 't_solicitudes_permisos.id_jefe_responsable (de otros)', COUNT(*)
FROM t_solicitudes_permisos WHERE id_jefe_responsable = @uid AND id_empleado <> @uid
UNION ALL
SELECT 't_permisos_aprobaciones.id_aprobador (permisos de otros)', COUNT(*)
FROM t_permisos_aprobaciones WHERE id_aprobador = @uid
UNION ALL
SELECT 't_sst_buzon_quejas.id_encargado/cancelado_por', COUNT(*)
FROM t_sst_buzon_quejas WHERE id_encargado = @uid OR cancelado_por = @uid
UNION ALL
SELECT 't_documentos_versiones.id_aprobador (docs de otros)', COUNT(*)
FROM t_documentos_versiones WHERE id_aprobador = @uid;

-- ---------------------------------------------------------------------
-- PASO 3: Limpieza de tablas SIN trigger de soft delete.
-- ---------------------------------------------------------------------
DELETE FROM t_permisos_evidencias
WHERE id_permiso IN (SELECT id_permiso FROM t_solicitudes_permisos WHERE id_empleado = @uid);

DELETE FROM t_permisos_aprobaciones
WHERE id_permiso IN (SELECT id_permiso FROM t_solicitudes_permisos WHERE id_empleado = @uid);

DELETE FROM t_dias_festivos_excepciones WHERE id_empleado = @uid;
DELETE FROM t_vacaciones_ajustes      WHERE id_empleado = @uid;
DELETE FROM t_sst_buzon_quejas        WHERE id_usuario = @uid;
DELETE FROM t_comunicados_vistos      WHERE id_usuario = @uid;
DELETE FROM t_remember_tokens         WHERE id_usuario = @uid;
DELETE FROM t_recuperacion_contrasena  WHERE id_usuario = @uid;
DELETE FROM t_intentos_correo         WHERE id_usuario = @uid;
DELETE FROM t_intentos_subida         WHERE id_usuario = @uid;
DELETE FROM t_registros_pendientes    WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 4: Respaldo de las filas maestras antes de borrar (prudencia).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bck_usuarios_pruebas LIKE t_usuarios;
INSERT INTO bck_usuarios_pruebas
SELECT * FROM t_usuarios WHERE id_usuario = @uid;

CREATE TABLE IF NOT EXISTS bck_empleados_pruebas LIKE t_empleados;
INSERT INTO bck_empleados_pruebas
SELECT * FROM t_empleados WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 5: DROP temporal de los triggers de soft delete implicados.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS tri_soft_delete_usuarios;
DROP TRIGGER IF EXISTS tri_soft_delete_usuarios_operaciones;
DROP TRIGGER IF EXISTS tri_soft_delete_empleados;
DROP TRIGGER IF EXISTS tri_soft_delete_contratos_empleados;
DROP TRIGGER IF EXISTS tri_soft_delete_nomina;
DROP TRIGGER IF EXISTS tri_soft_delete_afiliaciones_empleados;
DROP TRIGGER IF EXISTS tri_soft_delete_solicitudes_permisos;
DROP TRIGGER IF EXISTS tri_soft_delete_solicitudes_permisos_motivos;
DROP TRIGGER IF EXISTS tri_soft_delete_permisos_dias_aprobados;
DROP TRIGGER IF EXISTS tri_soft_delete_documentos_rrhh;
DROP TRIGGER IF EXISTS tri_soft_delete_documentos_versiones;

-- ---------------------------------------------------------------------
-- PASO 6: Borrado físico en orden (hijos antes que padres).
-- ---------------------------------------------------------------------
DELETE FROM t_documentos_versiones
WHERE id_documento IN (SELECT id_documento FROM t_documentos_rrhh WHERE id_usuario = @uid);

DELETE FROM t_documentos_rrhh WHERE id_usuario = @uid;

DELETE FROM t_solicitudes_permisos_motivos
WHERE id_permiso IN (SELECT id_permiso FROM t_solicitudes_permisos WHERE id_empleado = @uid);

DELETE FROM t_permisos_dias_aprobados WHERE id_empleado = @uid;

DELETE FROM t_solicitudes_permisos WHERE id_empleado = @uid;

DELETE FROM t_afiliaciones_empleados WHERE id_usuario = @uid;
DELETE FROM t_nomina                 WHERE id_usuario = @uid;
DELETE FROM t_contratos_empleados    WHERE id_usuario = @uid;
DELETE FROM t_usuarios_operaciones   WHERE id_usuario = @uid;
DELETE FROM t_empleados              WHERE id_usuario = @uid;
DELETE FROM t_usuarios               WHERE id_usuario = @uid;

-- ---------------------------------------------------------------------
-- PASO 7: Re-creación IDÉNTICA de los triggers (copiados 1:1 de
--         scripts/triggers/softdelete.sql).
-- ---------------------------------------------------------------------
DELIMITER //
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios
BEFORE DELETE ON t_usuarios
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_usuarios; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios_operaciones
BEFORE DELETE ON t_usuarios_operaciones
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_usuarios_operaciones; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_empleados
BEFORE DELETE ON t_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_empleados; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_contratos_empleados
BEFORE DELETE ON t_contratos_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_contratos_empleados; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_nomina
BEFORE DELETE ON t_nomina
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_nomina; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_afiliaciones_empleados
BEFORE DELETE ON t_afiliaciones_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_afiliaciones_empleados; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_solicitudes_permisos
BEFORE DELETE ON t_solicitudes_permisos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_solicitudes_permisos; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_solicitudes_permisos_motivos
BEFORE DELETE ON t_solicitudes_permisos_motivos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_solicitudes_permisos_motivos; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_permisos_dias_aprobados
BEFORE DELETE ON t_permisos_dias_aprobados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_permisos_dias_aprobados; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_documentos_rrhh
BEFORE DELETE ON t_documentos_rrhh
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_documentos_rrhh; utilice el borrado lógico.';
END//

CREATE OR REPLACE TRIGGER tri_soft_delete_documentos_versiones
BEFORE DELETE ON t_documentos_versiones
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_documentos_versiones; utilice el borrado lógico.';
END//
DELIMITER ;

-- ---------------------------------------------------------------------
-- PASO 8: Verificación final.
--   a) Los triggers deben aparecer listados (quedaron restaurados).
--   b) Deben quedar 0 filas en t_usuarios y t_empleados para ese id.
-- ---------------------------------------------------------------------
SHOW TRIGGERS LIKE 't_usuarios';
SHOW TRIGGERS LIKE 't_empleados';

SELECT COUNT(*) AS quedan_en_t_usuarios
FROM t_usuarios
WHERE id_usuario = @uid;

SELECT COUNT(*) AS quedan_en_t_empleados
FROM t_empleados
WHERE id_usuario = @uid;