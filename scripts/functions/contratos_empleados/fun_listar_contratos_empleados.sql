CREATE OR REPLACE FUNCTION fun_listar_contratos_empleados(
    wid_contrato t_contratos_empleados.id_contrato%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_contrato t_contratos_empleados%ROWTYPE;
BEGIN
    IF wid_contrato IS NULL OR wid_contrato <= 0 THEN
        RAISE NOTICE 'El ID del contrato no es valido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_contrato
    FROM t_contratos_empleados
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe contrato activo con ID %.', wid_contrato;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Contrato: %, Usuario: %, Area: %, Puesto: %, Inicio: %, Fin: %',
        wreg_contrato.id_contrato,
        wreg_contrato.id_usuario,
        wreg_contrato.id_area,
        wreg_contrato.puesto,
        wreg_contrato.fecha_inicio_puesto,
        wreg_contrato.fecha_fin_puesto;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;