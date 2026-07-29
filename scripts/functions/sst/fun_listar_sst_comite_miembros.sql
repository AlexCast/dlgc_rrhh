CREATE OR REPLACE FUNCTION fun_listar_sst_comite_miembros(
    wtipo_comite t_sst_comite_miembros.tipo_comite%TYPE DEFAULT NULL
) RETURNS TABLE (
    id_miembro INT,
    nombre_completo VARCHAR,
    cargo VARCHAR,
    tipo_comite VARCHAR,
    correo VARCHAR,
    telefono VARCHAR,
    orden_visualizacion INT,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT
        m.id_miembro,
        m.nombre_completo,
        m.cargo,
        m.tipo_comite,
        m.correo,
        m.telefono,
        m.orden_visualizacion,
        m.usr_insert,
        m.fec_insert,
        m.fec_delete
    FROM t_sst_comite_miembros m
    WHERE (wtipo_comite IS NULL OR TRIM(wtipo_comite) = '' OR m.tipo_comite = UPPER(TRIM(wtipo_comite)))
    ORDER BY m.tipo_comite, m.orden_visualizacion, m.nombre_completo;
END;
$$
LANGUAGE PLPGSQL;
