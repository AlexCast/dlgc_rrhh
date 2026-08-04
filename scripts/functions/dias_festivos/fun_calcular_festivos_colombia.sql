-- Calcula los 18 festivos civiles colombianos de un año (Ley 51 de 1983 / Ley Emiliani).
-- No incluye festivos de empresa: esos se administran aparte en t_dias_festivos (tipo_festivo = 'EMPRESA').
CREATE OR REPLACE FUNCTION fun_calcular_festivos_colombia(wanio INT)
RETURNS TABLE (fecha DATE, descripcion VARCHAR) AS
$$
DECLARE
    vpascua DATE;
BEGIN
    vpascua := fun_calcular_pascua(wanio);

    RETURN QUERY VALUES
        -- Fijos: siempre caen en su fecha calendario, sin importar el día de la semana.
        (make_date(wanio, 1, 1), 'Año Nuevo'::VARCHAR),
        (make_date(wanio, 5, 1), 'Día del Trabajo'::VARCHAR),
        (make_date(wanio, 7, 20), 'Día de la Independencia'::VARCHAR),
        (make_date(wanio, 8, 7), 'Batalla de Boyacá'::VARCHAR),
        (make_date(wanio, 12, 8), 'Inmaculada Concepción'::VARCHAR),
        (make_date(wanio, 12, 25), 'Navidad'::VARCHAR),

        -- Basados en Pascua, NO se trasladan de día (Semana Santa).
        (vpascua - 3, 'Jueves Santo'::VARCHAR),
        (vpascua - 2, 'Viernes Santo'::VARCHAR),

        -- Ley Emiliani: fijos que se trasladan al lunes siguiente.
        (fun_siguiente_lunes(make_date(wanio, 1, 6)), 'Reyes Magos'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 3, 19)), 'San José'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 6, 29)), 'San Pedro y San Pablo'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 8, 15)), 'Asunción de la Virgen'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 10, 12)), 'Día de la Raza'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 11, 1)), 'Todos los Santos'::VARCHAR),
        (fun_siguiente_lunes(make_date(wanio, 11, 11)), 'Independencia de Cartagena'::VARCHAR),

        -- Ley Emiliani: basados en Pascua y luego trasladados al lunes siguiente.
        (fun_siguiente_lunes(vpascua + 39), 'Ascensión del Señor'::VARCHAR),
        (fun_siguiente_lunes(vpascua + 60), 'Corpus Christi'::VARCHAR),
        (fun_siguiente_lunes(vpascua + 68), 'Sagrado Corazón'::VARCHAR);
END;
$$
LANGUAGE PLPGSQL IMMUTABLE;
