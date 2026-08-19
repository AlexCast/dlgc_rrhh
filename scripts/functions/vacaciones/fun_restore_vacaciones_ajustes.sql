-- Revierte la anulación de un ajuste de vacaciones (restaurar borrado lógico).
CREATE OR REPLACE FUNCTION fun_restore_vacaciones_ajustes(
    wid_ajuste t_vacaciones_ajustes.id_ajuste%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_vacaciones_ajustes
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_ajuste = wid_ajuste
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Ajuste de vacaciones % restaurado.', wid_ajuste;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró ajuste anulado con ID %.', wid_ajuste;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
