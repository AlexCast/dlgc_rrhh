-- Anula (borrado lógico) un ajuste manual de vacaciones cargado por error.
CREATE OR REPLACE FUNCTION fun_softdelete_vacaciones_ajustes(
    wid_ajuste t_vacaciones_ajustes.id_ajuste%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_vacaciones_ajustes
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_ajuste = wid_ajuste
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ajuste de vacaciones % anulado.', wid_ajuste;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró ajuste activo con ID %.', wid_ajuste;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
