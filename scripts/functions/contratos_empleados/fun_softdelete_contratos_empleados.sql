CREATE OR REPLACE FUNCTION fun_softdelete_contratos_empleados(
    wid_contrato t_contratos_empleados.id_contrato%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_contratos_empleados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Contrato % eliminado lógicamente.', wid_contrato;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró contrato activo con ID %.', wid_contrato;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;