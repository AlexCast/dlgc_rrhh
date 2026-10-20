DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_empleados(
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_jefe VARCHAR(20);
    DECLARE v_id_municipio INT;
    DECLARE v_fecha_ingreso DATE;
    DECLARE v_fecha_egreso DATE;
    DECLARE v_genero VARCHAR(10);
    DECLARE v_fecha_nacimiento DATE;
    DECLARE v_tipo_sangre VARCHAR(5);
    DECLARE v_estado_civil VARCHAR(20);
    DECLARE v_direccion_casa VARCHAR(100);
    DECLARE v_numero_celular VARCHAR(10);
    DECLARE v_foto_perfil VARCHAR(255);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN FALSE;
    END IF;

    SELECT
        id_usuario,
        id_jefe,
        id_municipio,
        fecha_ingreso,
        fecha_egreso,
        genero,
        fecha_nacimiento,
        tipo_sangre,
        estado_civil,
        direccion_casa,
        numero_celular,
        foto_perfil
    INTO 
        v_id_usuario,
        v_id_jefe,
        v_id_municipio,
        v_fecha_ingreso,
        v_fecha_egreso,
        v_genero,
        v_fecha_nacimiento,
        v_tipo_sangre,
        v_estado_civil,
        v_direccion_casa,
        v_numero_celular,
        v_foto_perfil
    FROM t_empleados
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;