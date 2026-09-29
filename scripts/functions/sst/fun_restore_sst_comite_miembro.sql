CREATE OR REPLACE FUNCTION fun_restore_sst_comite_miembro(
    wid_miembro t_sst_comite_miembros.id_miembro%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_sst_comite_miembros
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_miembro = wid_miembro
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Miembro % restaurado correctamente.', wid_miembro;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró miembro eliminado con ID %.', wid_miembro;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
