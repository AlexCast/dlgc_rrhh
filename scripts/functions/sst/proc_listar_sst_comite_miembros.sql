DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_sst_comite_miembros(
    IN wtipo_comite VARCHAR(30)
)
BEGIN
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
END //

DELIMITER ;