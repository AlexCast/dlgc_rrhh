DELIMITER //

-- Traslada una fecha al lunes siguiente (Ley Emiliani).
CREATE OR REPLACE FUNCTION fun_siguiente_lunes(wfecha DATE) RETURNS DATE
DETERMINISTIC
BEGIN
    -- DAYOFWEEK en MariaDB: 1=Dom, 2=Lun, 3=Mar, 4=Mie, 5=Jue, 6=Vie, 7=Sab
    -- La fórmula matemática estricta equivalente al EXTRACT(DOW) de Postgres:
    RETURN DATE_ADD(wfecha, INTERVAL ((9 - DAYOFWEEK(wfecha)) MOD 7) DAY);
END //

DELIMITER ;