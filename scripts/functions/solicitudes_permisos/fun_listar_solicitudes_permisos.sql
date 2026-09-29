CREATE OR REPLACE FUNCTION fun_listar_solicitudes_permisos(
    wid_permiso t_solicitudes_permisos.id_permiso%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_permiso t_solicitudes_permisos%ROWTYPE;
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RAISE NOTICE 'El ID del permiso no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_permiso
    FROM t_solicitudes_permisos
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe solicitud activa con ID %.', wid_permiso;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Permiso: %, Empleado: %, Estado: %, Inicio: %, Fin: %, Por horas: %',
        wreg_permiso.id_permiso,
        wreg_permiso.id_empleado,
        wreg_permiso.estado,
        wreg_permiso.fecha_inicio,
        wreg_permiso.fecha_fin,
        wreg_permiso.es_por_horas;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;