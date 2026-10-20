DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_sst_comite_miembro(
    wnombre_completo VARCHAR(100),
    wcargo VARCHAR(50),
    wtipo_comite VARCHAR(30),
    wcorreo VARCHAR(40),
    wtelefono VARCHAR(15),
    worden_visualizacion INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El tipo de comité no es válido o ocurrió un error al registrar.';

    IF wnombre_completo IS NULL OR LENGTH(TRIM(wnombre_completo)) < 3 THEN
        RETURN 'El nombre completo debe tener al menos 3 caracteres.';
    END IF;

    IF wcargo IS NULL OR LENGTH(TRIM(wcargo)) < 2 THEN
        RETURN 'El cargo es obligatorio.';
    END IF;

    IF wtipo_comite IS NULL OR TRIM(wtipo_comite) = '' THEN
        RETURN 'El tipo de comité es obligatorio.';
    END IF;

    INSERT INTO t_sst_comite_miembros (
        nombre_completo,
        cargo,
        tipo_comite,
        correo,
        telefono,
        orden_visualizacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_completo),
        TRIM(wcargo),
        UPPER(TRIM(wtipo_comite)),
        NULLIF(TRIM(wcorreo), ''),
        NULLIF(TRIM(wtelefono), ''),
        COALESCE(worden_visualizacion, 0),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Miembro del comité registrado correctamente.';
END //

DELIMITER ;