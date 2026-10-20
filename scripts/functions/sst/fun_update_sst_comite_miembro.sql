DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_sst_comite_miembro(
    wid_miembro INT,
    wnombre_completo VARCHAR(100),
    wcargo VARCHAR(50),
    wtipo_comite VARCHAR(30),
    wcorreo VARCHAR(40),
    wtelefono VARCHAR(15),
    worden_visualizacion INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El tipo de comité no es válido o ocurrió un error al actualizar.';

    IF wid_miembro IS NULL OR wid_miembro <= 0 THEN
        RETURN 'El ID del miembro no es válido.';
    END IF;

    IF wnombre_completo IS NULL OR LENGTH(TRIM(wnombre_completo)) < 3 THEN
        RETURN 'El nombre completo debe tener al menos 3 caracteres.';
    END IF;

    IF wcargo IS NULL OR LENGTH(TRIM(wcargo)) < 2 THEN
        RETURN 'El cargo es obligatorio.';
    END IF;

    IF wtipo_comite IS NULL OR TRIM(wtipo_comite) = '' THEN
        RETURN 'El tipo de comité es obligatorio.';
    END IF;

    UPDATE t_sst_comite_miembros
    SET nombre_completo = TRIM(wnombre_completo),
        cargo = TRIM(wcargo),
        tipo_comite = UPPER(TRIM(wtipo_comite)),
        correo = NULLIF(TRIM(wcorreo), ''),
        telefono = NULLIF(TRIM(wtelefono), ''),
        orden_visualizacion = COALESCE(worden_visualizacion, 0),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_miembro = wid_miembro
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Miembro del comité actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el miembro activo para actualizar.';
END //

DELIMITER ;