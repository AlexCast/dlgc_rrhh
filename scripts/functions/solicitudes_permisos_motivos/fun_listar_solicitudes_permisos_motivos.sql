DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_solicitudes_permisos_motivos(
    wid_permiso INT,
    wid_tipo_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_permiso INT;
    DECLARE v_id_tipo_permiso INT;
    DECLARE v_detalle_motivo VARCHAR(255);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_permiso IS NULL OR wid_permiso <= 0 OR wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_permiso, id_tipo_permiso, detalle_motivo
    INTO v_id_permiso, v_id_tipo_permiso, v_detalle_motivo
    FROM t_solicitudes_permisos_motivos
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;