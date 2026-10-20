DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_usuarios(
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_rol INT;
    DECLARE v_username VARCHAR(30);
    DECLARE v_tipo_documento VARCHAR(20);
    DECLARE v_primer_nombre VARCHAR(30);
    DECLARE v_segundo_nombre VARCHAR(30);
    DECLARE v_primer_apellido VARCHAR(30);
    DECLARE v_segundo_apellido VARCHAR(30);
    DECLARE v_correo VARCHAR(40);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN FALSE;
    END IF;

    SELECT id_usuario, id_rol, username, tipo_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo
    INTO v_id_usuario, v_id_rol, v_username, v_tipo_documento, v_primer_nombre, v_segundo_nombre, v_primer_apellido, v_segundo_apellido, v_correo
    FROM t_usuarios
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;