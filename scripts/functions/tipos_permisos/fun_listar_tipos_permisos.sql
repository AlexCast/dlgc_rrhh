DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_tipos_permisos(
    wid_tipo_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_tipo_permiso INT;
    DECLARE v_nombre_tipo VARCHAR(50);
    DECLARE v_descuenta_tiempo BOOLEAN;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_tipo_permiso, nombre_tipo, descuenta_tiempo
    INTO v_id_tipo_permiso, v_nombre_tipo, v_descuenta_tiempo
    FROM t_tipos_permisos
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;