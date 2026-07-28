CREATE OR REPLACE FUNCTION fun_listar_solicitudes_permisos_motivos(
    wid_permiso t_solicitudes_permisos_motivos.id_permiso%TYPE,
    wid_tipo_permiso t_solicitudes_permisos_motivos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_motivo t_solicitudes_permisos_motivos%ROWTYPE;
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 OR wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RAISE NOTICE 'Los IDs de permiso y tipo de permiso deben ser válidos.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_motivo
    FROM t_solicitudes_permisos_motivos
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe relación activa entre permiso % y tipo %.', wid_permiso, wid_tipo_permiso;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Permiso: %, Tipo: %, Detalle: %',
        wreg_motivo.id_permiso,
        wreg_motivo.id_tipo_permiso,
        wreg_motivo.detalle_motivo;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;