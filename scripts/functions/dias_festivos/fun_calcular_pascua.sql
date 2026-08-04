-- Calcula la fecha del Domingo de Pascua para un año dado (algoritmo de Gauss/anónimo gregoriano).
-- Es la base para ubicar los festivos móviles colombianos (Jueves/Viernes Santo, Ascensión,
-- Corpus Christi, Sagrado Corazón), que se calculan como desplazamientos desde esta fecha.
CREATE OR REPLACE FUNCTION fun_calcular_pascua(wanio INT) RETURNS DATE AS
$$
DECLARE
    a INT; b INT; c INT; d INT; e INT; f INT; g INT; h INT; i INT; k INT; l INT; m INT;
    vmes INT; vdia INT;
BEGIN
    a := wanio % 19;
    b := wanio / 100;
    c := wanio % 100;
    d := b / 4;
    e := b % 4;
    f := (b + 8) / 25;
    g := (b - f + 1) / 3;
    h := (19 * a + b - d - g + 15) % 30;
    i := c / 4;
    k := c % 4;
    l := (32 + 2 * e + 2 * i - h - k) % 7;
    m := (a + 11 * h + 22 * l) / 451;
    vmes := (h + l - 7 * m + 114) / 31;
    vdia := ((h + l - 7 * m + 114) % 31) + 1;

    RETURN make_date(wanio, vmes, vdia);
END;
$$
LANGUAGE PLPGSQL IMMUTABLE;
