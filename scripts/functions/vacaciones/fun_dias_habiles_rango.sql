-- Cuenta los días hábiles (lunes a sábado, excluyendo domingos y CUALQUIER día
-- registrado en t_dias_festivos, nacional o de empresa) entre dos fechas, ambas
-- incluidas. Misma regla usada históricamente en app/permiso_reporte.php.
CREATE OR REPLACE FUNCTION fun_dias_habiles_rango(
    wfecha_inicio DATE,
    wfecha_fin DATE
) RETURNS INT AS
$$
DECLARE
    vdias INT;
BEGIN
    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL OR wfecha_fin < wfecha_inicio THEN
        RETURN 0;
    END IF;

    SELECT COUNT(*) INTO vdias
    FROM generate_series(wfecha_inicio, wfecha_fin, interval '1 day') gs(dia)
    WHERE EXTRACT(DOW FROM gs.dia) <> 0
      AND NOT EXISTS (
          SELECT 1 FROM t_dias_festivos df
          WHERE df.fecha = gs.dia::date AND df.fec_delete IS NULL
      );

    RETURN vdias;
END;
$$
LANGUAGE PLPGSQL STABLE;
