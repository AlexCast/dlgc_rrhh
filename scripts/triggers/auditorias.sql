/*---------------------------------------------*/
/* GRUPO 1 - AUDITORÍA                         */
/* Registra automáticamente quién y cuándo     */
/* insertó o modificó cada fila. Rellena       */
/* usr_insert/fec_insert en INSERT y           */
/* usr_update/fec_update en UPDATE.            */
/*                                             */
/* Adaptación MariaDB: no existen funciones     */
/* TRIGGER, por lo que la lógica de            */
/* fun_audit_tablas() queda integrada en cada  */
/* trigger (BEFORE INSERT y BEFORE UPDATE).    */
/*---------------------------------------------*/

DELIMITER //

CREATE OR REPLACE FUNCTION fun_audit_actor()
RETURNS VARCHAR(255)
    RETURN COALESCE(NULLIF(@app_current_user, ''), CURRENT_USER());
//

-- t_usuarios
CREATE OR REPLACE TRIGGER tri_audit_usuarios_ins
BEFORE INSERT ON t_usuarios
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_usuarios_upd
BEFORE UPDATE ON t_usuarios
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_afiliaciones_empleados
CREATE OR REPLACE TRIGGER tri_audit_afiliaciones_empleados_ins
BEFORE INSERT ON t_afiliaciones_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_afiliaciones_empleados_upd
BEFORE UPDATE ON t_afiliaciones_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_areas
CREATE OR REPLACE TRIGGER tri_audit_areas_ins
BEFORE INSERT ON t_areas
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_areas_upd
BEFORE UPDATE ON t_areas
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_arl
CREATE OR REPLACE TRIGGER tri_audit_arl_ins
BEFORE INSERT ON t_arl
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_arl_upd
BEFORE UPDATE ON t_arl
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_bancos
CREATE OR REPLACE TRIGGER tri_audit_bancos_ins
BEFORE INSERT ON t_bancos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_bancos_upd
BEFORE UPDATE ON t_bancos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_caja_compensacion
CREATE OR REPLACE TRIGGER tri_audit_caja_compensacion_ins
BEFORE INSERT ON t_caja_compensacion
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_caja_compensacion_upd
BEFORE UPDATE ON t_caja_compensacion
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_cesantias
CREATE OR REPLACE TRIGGER tri_audit_cesantias_ins
BEFORE INSERT ON t_cesantias
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_cesantias_upd
BEFORE UPDATE ON t_cesantias
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_comunicados
CREATE OR REPLACE TRIGGER tri_audit_comunicados_ins
BEFORE INSERT ON t_comunicados
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_comunicados_upd
BEFORE UPDATE ON t_comunicados
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_contratos_empleados
CREATE OR REPLACE TRIGGER tri_audit_contratos_empleados_ins
BEFORE INSERT ON t_contratos_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_contratos_empleados_upd
BEFORE UPDATE ON t_contratos_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_dias_festivos
CREATE OR REPLACE TRIGGER tri_audit_dias_festivos_ins
BEFORE INSERT ON t_dias_festivos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_dias_festivos_upd
BEFORE UPDATE ON t_dias_festivos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_empleados
CREATE OR REPLACE TRIGGER tri_audit_empleados_ins
BEFORE INSERT ON t_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_empleados_upd
BEFORE UPDATE ON t_empleados
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_eps
CREATE OR REPLACE TRIGGER tri_audit_eps_ins
BEFORE INSERT ON t_eps
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_eps_upd
BEFORE UPDATE ON t_eps
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_modulos
CREATE OR REPLACE TRIGGER tri_audit_modulos_ins
BEFORE INSERT ON t_modulos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_modulos_upd
BEFORE UPDATE ON t_modulos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_nomina
CREATE OR REPLACE TRIGGER tri_audit_nomina_ins
BEFORE INSERT ON t_nomina
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_nomina_upd
BEFORE UPDATE ON t_nomina
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_pension
CREATE OR REPLACE TRIGGER tri_audit_pension_ins
BEFORE INSERT ON t_pension
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_pension_upd
BEFORE UPDATE ON t_pension
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_permisos_dias_aprobados
CREATE OR REPLACE TRIGGER tri_audit_permisos_dias_aprobados_ins
BEFORE INSERT ON t_permisos_dias_aprobados
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_permisos_dias_aprobados_upd
BEFORE UPDATE ON t_permisos_dias_aprobados
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_solicitudes_permisos
CREATE OR REPLACE TRIGGER tri_audit_solicitudes_permisos_ins
BEFORE INSERT ON t_solicitudes_permisos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_solicitudes_permisos_upd
BEFORE UPDATE ON t_solicitudes_permisos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_tipos_permisos
CREATE OR REPLACE TRIGGER tri_audit_tipos_permisos_ins
BEFORE INSERT ON t_tipos_permisos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_tipos_permisos_upd
BEFORE UPDATE ON t_tipos_permisos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_solicitudes_permisos_motivos
CREATE OR REPLACE TRIGGER tri_audit_solicitudes_permisos_motivos_ins
BEFORE INSERT ON t_solicitudes_permisos_motivos
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_solicitudes_permisos_motivos_upd
BEFORE UPDATE ON t_solicitudes_permisos_motivos
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_roles
CREATE OR REPLACE TRIGGER tri_audit_roles_ins
BEFORE INSERT ON t_roles
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_roles_upd
BEFORE UPDATE ON t_roles
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_operaciones
CREATE OR REPLACE TRIGGER tri_audit_operaciones_ins
BEFORE INSERT ON t_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_operaciones_upd
BEFORE UPDATE ON t_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_roles_operaciones
CREATE OR REPLACE TRIGGER tri_audit_roles_operaciones_ins
BEFORE INSERT ON t_roles_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_roles_operaciones_upd
BEFORE UPDATE ON t_roles_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_usuarios_operaciones
CREATE OR REPLACE TRIGGER tri_audit_usuarios_operaciones_ins
BEFORE INSERT ON t_usuarios_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_usuarios_operaciones_upd
BEFORE UPDATE ON t_usuarios_operaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_recuperacion_contrasena
CREATE OR REPLACE TRIGGER tri_audit_recuperacion_contrasena_ins
BEFORE INSERT ON t_recuperacion_contrasena
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_recuperacion_contrasena_upd
BEFORE UPDATE ON t_recuperacion_contrasena
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_verificacion_correo
CREATE OR REPLACE TRIGGER tri_audit_verificacion_correo_ins
BEFORE INSERT ON t_verificacion_correo
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_verificacion_correo_upd
BEFORE UPDATE ON t_verificacion_correo
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_intentos_correo
CREATE OR REPLACE TRIGGER tri_audit_intentos_correo_ins
BEFORE INSERT ON t_intentos_correo
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_intentos_correo_upd
BEFORE UPDATE ON t_intentos_correo
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_remember_tokens
CREATE OR REPLACE TRIGGER tri_audit_remember_tokens_ins
BEFORE INSERT ON t_remember_tokens
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_remember_tokens_upd
BEFORE UPDATE ON t_remember_tokens
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_registros_pendientes
CREATE OR REPLACE TRIGGER tri_audit_registros_pendientes_ins
BEFORE INSERT ON t_registros_pendientes
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_registros_pendientes_upd
BEFORE UPDATE ON t_registros_pendientes
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_sst_comite_miembros
CREATE OR REPLACE TRIGGER tri_audit_sst_comite_miembros_ins
BEFORE INSERT ON t_sst_comite_miembros
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_sst_comite_miembros_upd
BEFORE UPDATE ON t_sst_comite_miembros
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_sst_buzon_quejas
CREATE OR REPLACE TRIGGER tri_audit_sst_buzon_quejas_ins
BEFORE INSERT ON t_sst_buzon_quejas
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_sst_buzon_quejas_upd
BEFORE UPDATE ON t_sst_buzon_quejas
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_permisos_aprobaciones
CREATE OR REPLACE TRIGGER tri_audit_permisos_aprobaciones_ins
BEFORE INSERT ON t_permisos_aprobaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_permisos_aprobaciones_upd
BEFORE UPDATE ON t_permisos_aprobaciones
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_permisos_evidencias
CREATE OR REPLACE TRIGGER tri_audit_permisos_evidencias_ins
BEFORE INSERT ON t_permisos_evidencias
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_permisos_evidencias_upd
BEFORE UPDATE ON t_permisos_evidencias
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_dias_festivos_excepciones
CREATE OR REPLACE TRIGGER tri_audit_dias_festivos_excepciones_ins
BEFORE INSERT ON t_dias_festivos_excepciones
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_dias_festivos_excepciones_upd
BEFORE UPDATE ON t_dias_festivos_excepciones
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

-- t_vacaciones_ajustes
CREATE OR REPLACE TRIGGER tri_audit_vacaciones_ajustes_ins
BEFORE INSERT ON t_vacaciones_ajustes
FOR EACH ROW
BEGIN
    SET NEW.usr_insert = fun_audit_actor();
    SET NEW.fec_insert = CURRENT_TIMESTAMP;
END//

CREATE OR REPLACE TRIGGER tri_audit_vacaciones_ajustes_upd
BEFORE UPDATE ON t_vacaciones_ajustes
FOR EACH ROW
BEGIN
    SET NEW.usr_update = fun_audit_actor();
    SET NEW.fec_update = CURRENT_TIMESTAMP;
END//

DELIMITER ;
