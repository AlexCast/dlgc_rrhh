CREATE OR REPLACE FUNCTION fun_update_sst_comite_miembro(
    wid_miembro t_sst_comite_miembros.id_miembro%TYPE,
    wnombre_completo t_sst_comite_miembros.nombre_completo%TYPE,
    wcargo t_sst_comite_miembros.cargo%TYPE,
    wtipo_comite t_sst_comite_miembros.tipo_comite%TYPE,
    wcorreo t_sst_comite_miembros.correo%TYPE DEFAULT NULL,
    wtelefono t_sst_comite_miembros.telefono%TYPE DEFAULT NULL,
    worden_visualizacion t_sst_comite_miembros.orden_visualizacion%TYPE DEFAULT 0
) RETURNS VARCHAR AS
$$
BEGIN
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
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_miembro = wid_miembro
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Miembro del comité actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el miembro activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de comité indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el miembro del comité.';
END;
$$
LANGUAGE PLPGSQL;
