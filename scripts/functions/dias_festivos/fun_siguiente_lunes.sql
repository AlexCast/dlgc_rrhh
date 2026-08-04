-- Traslada una fecha al lunes siguiente (o la deja igual si ya es lunes).
-- Implementa la Ley Emiliani (Ley 51 de 1983): varios festivos colombianos
-- se trasladan siempre al lunes de la semana en que caen.
CREATE OR REPLACE FUNCTION fun_siguiente_lunes(wfecha DATE) RETURNS DATE AS
$$
DECLARE
    vdia_semana INT; -- EXTRACT(DOW): 0=domingo, 1=lunes, ..., 6=sábado
BEGIN
    vdia_semana := EXTRACT(DOW FROM wfecha);
    RETURN wfecha + (((8 - vdia_semana) % 7) || ' days')::INTERVAL;
END;
$$
LANGUAGE PLPGSQL IMMUTABLE;
