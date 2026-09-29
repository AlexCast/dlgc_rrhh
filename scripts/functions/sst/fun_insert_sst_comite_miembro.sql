CREATE OR REPLACE FUNCTION fun_insert_sst_comite_miembro(
    wnombre_completo t_sst_comite_miembros.nombre_completo%TYPE,
    wcargo t_sst_comite_miembros.cargo%TYPE,
    wtipo_comite t_sst_comite_miembros.tipo_comite%TYPE,
    wcorreo t_sst_comite_miembros.correo%TYPE DEFAULT NULL,
    wtelefono t_sst_comite_miembros.telefono%TYPE DEFAULT NULL,
    worden_visualizacion t_sst_comite_miembros.orden_visualizacion%TYPE DEFAULT 0
) RETURNS VARCHAR AS
$$
BEGIN
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
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Miembro del comité registrado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de comité indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al registrar el miembro del comité.';
END;
$$
LANGUAGE PLPGSQL;
