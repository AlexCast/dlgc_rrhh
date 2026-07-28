CREATE OR REPLACE FUNCTION fun_listar_tipos_permisos(
    wid_tipo_permiso t_tipos_permisos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_tipo t_tipos_permisos%ROWTYPE;
BEGIN
    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RAISE NOTICE 'El ID del tipo de permiso no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_tipo
    FROM t_tipos_permisos
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe tipo de permiso activo con ID %.', wid_tipo_permiso;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Tipo: %, Descuenta tiempo: %',
        wreg_tipo.id_tipo_permiso,
        wreg_tipo.nombre_tipo,
        wreg_tipo.descuenta_tiempo;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;