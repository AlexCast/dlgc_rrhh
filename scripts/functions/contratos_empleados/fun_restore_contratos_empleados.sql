CREATE OR REPLACE FUNCTION fun_restore_contratos_empleados(
    wid_contrato t_contratos_empleados.id_contrato%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_contratos_empleados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Contrato % restaurado correctamente.', wid_contrato;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró contrato eliminado con ID %.', wid_contrato;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;