DELIMITER //

-- Calcula la fecha del Domingo de Pascua para un año dado (algoritmo de Gauss).
CREATE OR REPLACE FUNCTION fun_calcular_pascua(wanio INT) RETURNS DATE
DETERMINISTIC
BEGIN
    DECLARE a, b, c, d, e, f, g, h, i, k, l, m INT;
    DECLARE vmes, vdia INT;
    
    SET a = wanio MOD 19;
    SET b = wanio DIV 100;
    SET c = wanio MOD 100;
    SET d = b DIV 4;
    SET e = b MOD 4;
    SET f = (b + 8) DIV 25;
    SET g = (b - f + 1) DIV 3;
    SET h = (19 * a + b - d - g + 15) MOD 30;
    SET i = c DIV 4;
    SET k = c MOD 4;
    SET l = (32 + 2 * e + 2 * i - h - k) MOD 7;
    SET m = (a + 11 * h + 22 * l) DIV 451;
    SET vmes = (h + l - 7 * m + 114) DIV 31;
    SET vdia = ((h + l - 7 * m + 114) MOD 31) + 1;

    RETURN CAST(CONCAT(wanio, '-', LPAD(vmes, 2, '0'), '-', LPAD(vdia, 2, '0')) AS DATE);
END //

DELIMITER ;