/*---------------------------------------------*/
/* GRUPO 2 - SOFT DELETE                       */
/* Intercepta el DELETE y evita el borrado      */
/* físico. El borrado lógico (usr_delete /      */
/* fec_delete) lo realizan las funciones        */
/* fun_softdelete_* llamadas desde la app.      */
/*                                             */
/* Adaptación MariaDB: un trigger no puede      */
/* modificar (UPDATE/DELETE) la tabla sobre la  */
/* que está definido (error 1442). Por eso      */
/* estos triggers actúan como guarda y bloquean */
/* el DELETE físico mediante SIGNAL.            */
/*---------------------------------------------*/

DELIMITER //

-- t_usuarios
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios
BEFORE DELETE ON t_usuarios
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_usuarios; utilice el borrado lógico.';
END//

-- t_afiliaciones_empleados
CREATE OR REPLACE TRIGGER tri_soft_delete_afiliaciones_empleados
BEFORE DELETE ON t_afiliaciones_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_afiliaciones_empleados; utilice el borrado lógico.';
END//

-- t_areas
CREATE OR REPLACE TRIGGER tri_soft_delete_areas
BEFORE DELETE ON t_areas
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_areas; utilice el borrado lógico.';
END//

-- t_arl
CREATE OR REPLACE TRIGGER tri_soft_delete_arl
BEFORE DELETE ON t_arl
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_arl; utilice el borrado lógico.';
END//

-- t_bancos
CREATE OR REPLACE TRIGGER tri_soft_delete_bancos
BEFORE DELETE ON t_bancos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_bancos; utilice el borrado lógico.';
END//

-- t_caja_compensacion
CREATE OR REPLACE TRIGGER tri_soft_delete_caja_compensacion
BEFORE DELETE ON t_caja_compensacion
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_caja_compensacion; utilice el borrado lógico.';
END//

-- t_cesantias
CREATE OR REPLACE TRIGGER tri_soft_delete_cesantias
BEFORE DELETE ON t_cesantias
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_cesantias; utilice el borrado lógico.';
END//

-- t_comunicados
CREATE OR REPLACE TRIGGER tri_soft_delete_comunicados
BEFORE DELETE ON t_comunicados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_comunicados; utilice el borrado lógico.';
END//

-- t_contratos_empleados
CREATE OR REPLACE TRIGGER tri_soft_delete_contratos_empleados
BEFORE DELETE ON t_contratos_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_contratos_empleados; utilice el borrado lógico.';
END//

-- t_dias_festivos
CREATE OR REPLACE TRIGGER tri_soft_delete_dias_festivos
BEFORE DELETE ON t_dias_festivos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_dias_festivos; utilice el borrado lógico.';
END//

-- t_empleados
CREATE OR REPLACE TRIGGER tri_soft_delete_empleados
BEFORE DELETE ON t_empleados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_empleados; utilice el borrado lógico.';
END//

-- t_eps
CREATE OR REPLACE TRIGGER tri_soft_delete_eps
BEFORE DELETE ON t_eps
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_eps; utilice el borrado lógico.';
END//

-- t_modulos
CREATE OR REPLACE TRIGGER tri_soft_delete_modulos
BEFORE DELETE ON t_modulos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_modulos; utilice el borrado lógico.';
END//

-- t_nomina
CREATE OR REPLACE TRIGGER tri_soft_delete_nomina
BEFORE DELETE ON t_nomina
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_nomina; utilice el borrado lógico.';
END//

-- t_pension
CREATE OR REPLACE TRIGGER tri_soft_delete_pension
BEFORE DELETE ON t_pension
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_pension; utilice el borrado lógico.';
END//

-- t_permisos_dias_aprobados
CREATE OR REPLACE TRIGGER tri_soft_delete_permisos_dias_aprobados
BEFORE DELETE ON t_permisos_dias_aprobados
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_permisos_dias_aprobados; utilice el borrado lógico.';
END//

-- t_solicitudes_permisos
CREATE OR REPLACE TRIGGER tri_soft_delete_solicitudes_permisos
BEFORE DELETE ON t_solicitudes_permisos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_solicitudes_permisos; utilice el borrado lógico.';
END//

-- t_tipos_permisos
CREATE OR REPLACE TRIGGER tri_soft_delete_tipos_permisos
BEFORE DELETE ON t_tipos_permisos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_tipos_permisos; utilice el borrado lógico.';
END//

-- t_solicitudes_permisos_motivos
CREATE OR REPLACE TRIGGER tri_soft_delete_solicitudes_permisos_motivos
BEFORE DELETE ON t_solicitudes_permisos_motivos
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_solicitudes_permisos_motivos; utilice el borrado lógico.';
END//

-- t_roles
CREATE OR REPLACE TRIGGER tri_soft_delete_roles
BEFORE DELETE ON t_roles
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_roles; utilice el borrado lógico.';
END//

-- t_operaciones
CREATE OR REPLACE TRIGGER tri_soft_delete_operaciones
BEFORE DELETE ON t_operaciones
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_operaciones; utilice el borrado lógico.';
END//

-- t_roles_operaciones
CREATE OR REPLACE TRIGGER tri_soft_delete_roles_operaciones
BEFORE DELETE ON t_roles_operaciones
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_roles_operaciones; utilice el borrado lógico.';
END//

-- t_usuarios_operaciones
CREATE OR REPLACE TRIGGER tri_soft_delete_usuarios_operaciones
BEFORE DELETE ON t_usuarios_operaciones
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Borrado físico no permitido en t_usuarios_operaciones; utilice el borrado lógico.';
END//

DELIMITER ;
