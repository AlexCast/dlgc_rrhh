/*---------------------------------------------*/
/* GRUPO 2 - SOFT DELETE                       */
/* Intercepta el DELETE antes de ejecutarse y  */
/* en su lugar marca la fila con usr_delete y  */
/* fec_delete (borrado lógico). El registro    */
/* permanece en la tabla pero queda inactivo.  */
/*---------------------------------------------*/
CREATE OR REPLACE FUNCTION fun_soft_delete_tablas() RETURNS TRIGGER AS
$$
BEGIN
    -- Actualizar la misma fila con datos de borrado
    EXECUTE format('UPDATE %I SET usr_delete = $1, fec_delete = CURRENT_TIMESTAMP WHERE ctid = $2', TG_TABLE_NAME)
    USING fun_audit_actor(), OLD.ctid;

    RETURN NULL; -- Evita el DELETE real
END;
$$ LANGUAGE plpgsql;

-- Soft delete: marca como eliminado en tab_usuarios (no borra físicamente)
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE DELETE ON t_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_soft_delete_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_afiliaciones_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_areas
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_arl
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_bancos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_caja_compensacion
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_cesantias
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_comunicados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_contratos_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_dias_festivos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_empleados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_eps
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_modulos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_nomina
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_pension
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_permisos_dias_aprobados
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_solicitudes_permisos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_tipos_permisos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_usuarios
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_solicitudes_permisos_motivos
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_roles
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_roles_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios BEFORE INSERT OR UPDATE ON t_usuarios_operaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();
