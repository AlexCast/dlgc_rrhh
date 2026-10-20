DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_solicitudes_permisos(
    wid_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_permiso INT;
    DECLARE v_id_empleado VARCHAR(20);
    DECLARE v_estado VARCHAR(20);
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    DECLARE v_es_por_horas BOOLEAN;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN FALSE;
    END IF;

    -- Eliminamos el SELECT * declarando columnas explícitamente
    SELECT id_permiso, id_empleado, estado, fecha_inicio, fecha_fin, es_por_horas
    INTO v_id_permiso, v_id_empleado, v_estado, v_fecha_inicio, v_fecha_fin, v_es_por_horas
    FROM t_solicitudes_permisos
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;