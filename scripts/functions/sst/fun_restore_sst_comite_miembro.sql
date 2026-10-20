DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_sst_comite_miembro(
    wid_miembro INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_sst_comite_miembros
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_miembro = wid_miembro
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;