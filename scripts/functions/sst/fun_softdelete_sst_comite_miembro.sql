CREATE OR REPLACE FUNCTION fun_softdelete_sst_comite_miembro(
    wid_miembro t_sst_comite_miembros.id_miembro%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_sst_comite_miembros
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_miembro = wid_miembro
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Miembro % eliminado lógicamente.', wid_miembro;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró miembro activo con ID %.', wid_miembro;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
