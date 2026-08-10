/*---------------------------------------------*/
/* GRUPO 1 - AUDITORÍA                         */
/* Registra automáticamente quién y cuándo     */
/* insertó o modificó cada fila. Rellena       */
/* usr_insert/fec_insert en INSERT y           */
/* usr_update/fec_update en UPDATE.            */
/*---------------------------------------------*/
CREATE OR REPLACE FUNCTION fun_audit_actor() RETURNS VARCHAR AS
$$
DECLARE
    v_actor VARCHAR;
BEGIN
    v_actor := NULLIF(current_setting('app.current_user', true), '');
    IF v_actor IS NULL THEN
        v_actor := CURRENT_USER;
    END IF;
    RETURN v_actor;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION fun_audit_tablas() RETURNS TRIGGER AS
$$
    BEGIN
        IF TG_OP = 'INSERT' THEN
            NEW.usr_insert = fun_audit_actor();
            NEW.fec_insert = CURRENT_TIMESTAMP;
            RETURN NEW;
        END IF;
        IF TG_OP = 'UPDATE' THEN
            NEW.usr_update = fun_audit_actor();
            NEW.fec_update = CURRENT_TIMESTAMP;
            RETURN NEW;
        END IF;
    END;
$$
LANGUAGE PLPGSQL;

-- Auditoría: registra insert/update en t_usuarios
CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_afiliaciones_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_areas
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_arl
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_bancos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_caja_compensacion
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_cesantias
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_comunicados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_contratos_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_dias_festivos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_eps
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_modulos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_nomina
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_pension
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_permisos_dias_aprobados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_solicitudes_permisos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_tipos_permisos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_solicitudes_permisos_motivos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_roles
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_roles_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_usuarios_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_recuperacion_contrasena
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_verificacion_correo
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_intentos_correo
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_remember_tokens
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_registros_pendientes
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_sst_comite_miembros
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_sst_buzon_quejas
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_permisos_aprobaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_permisos_evidencias
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_dias_festivos_excepciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_vacaciones_ajustes
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();