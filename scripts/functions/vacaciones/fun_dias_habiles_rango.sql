DELIMITER //

CREATE OR REPLACE FUNCTION fun_dias_habiles_rango(
    wfecha_inicio DATE,
    wfecha_fin DATE
) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE vdias INT DEFAULT 0;
    DECLARE v_current DATE;
    DECLARE v_is_festivo INT;

    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL OR wfecha_fin < wfecha_inicio THEN
        RETURN 0;
    END IF;

    SET v_current = wfecha_inicio;

    WHILE v_current <= wfecha_fin DO
        -- DAYOFWEEK en MariaDB: 1 = Domingo. Excluimos los domingos.
        IF DAYOFWEEK(v_current) <> 1 THEN
            SELECT COUNT(*) INTO v_is_festivo
            FROM t_dias_festivos
            WHERE fecha = v_current AND fec_delete IS NULL;

            IF v_is_festivo = 0 THEN
                SET vdias = vdias + 1;
            END IF;
        END IF;
        
        SET v_current = DATE_ADD(v_current, INTERVAL 1 DAY);
    END WHILE;

    RETURN vdias;
END //

DELIMITER ;