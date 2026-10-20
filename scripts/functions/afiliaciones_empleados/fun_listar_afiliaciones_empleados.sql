DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_afiliaciones_empleados(
    wid_usuario VARCHAR(20),
    wid_eps INT,
    wid_arl INT,
    wid_caja INT,
    wid_pension INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_eps INT;
    DECLARE v_id_arl INT;
    DECLARE v_id_caja INT;
    DECLARE v_id_pension INT;
    DECLARE v_id_cesantia INT;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    SELECT id_usuario, id_eps, id_arl, id_caja, id_pension, id_cesantia
    INTO v_id_usuario, v_id_eps, v_id_arl, v_id_caja, v_id_pension, v_id_cesantia
    FROM t_afiliaciones_empleados
    WHERE id_usuario = wid_usuario
      AND id_eps = wid_eps
      AND id_arl = wid_arl
      AND id_caja = wid_caja
      AND id_pension = wid_pension
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;