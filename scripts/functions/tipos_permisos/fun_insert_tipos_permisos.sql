CREATE OR REPLACE FUNCTION fun_insert_tipos_permisos(
    wnombre_tipo t_tipos_permisos.nombre_tipo%TYPE,
    wdescuenta_tiempo t_tipos_permisos.descuenta_tiempo%TYPE DEFAULT FALSE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wnombre_tipo IS NULL OR LENGTH(TRIM(wnombre_tipo)) < 3 THEN
        RETURN 'El nombre del tipo de permiso debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_tipos_permisos (
        nombre_tipo,
        descuenta_tiempo,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_tipo),
        COALESCE(wdescuenta_tiempo, FALSE),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Tipo de permiso insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El tipo de permiso ya existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el tipo de permiso.';
END;
$$
LANGUAGE PLPGSQL;