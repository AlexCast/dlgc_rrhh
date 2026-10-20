DELIMITER //

-- Siembra en t_dias_festivos los festivos civiles colombianos
CREATE OR REPLACE FUNCTION fun_sembrar_festivos_colombia(wanio INT) RETURNS VARCHAR(255)
BEGIN
    DECLARE vactor VARCHAR(255);
    DECLARE vinsertados INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN CONCAT('Ocurrió un error al sembrar los festivos del año ', wanio, '.');

    IF wanio IS NULL OR wanio < 2000 OR wanio > 2100 THEN
        RETURN 'El año indicado no es válido.';
    END IF;

    SET vactor = COALESCE(@app_current_user, CURRENT_USER());

    -- Llamar al procedimiento del archivo 8 para crear la tabla temporal en memoria
    CALL proc_calcular_festivos_colombia(wanio);

    -- Volcar los registros calculados a la tabla oficial. INSERT IGNORE equivale al ON CONFLICT DO NOTHING.
    INSERT IGNORE INTO t_dias_festivos (fecha, descripcion, tipo_festivo, descuenta_salario, usr_insert, fec_insert)
    SELECT fecha, descripcion, 'NACIONAL', FALSE, vactor, CURRENT_TIMESTAMP
    FROM tmp_festivos;

    SET vinsertados = ROW_COUNT();

    -- La tabla temporal es de sesión y se descarta sola al cerrar la conexión;
    -- DROP TEMPORARY TABLE provocaría un commit implícito no permitido en funciones.

    RETURN CONCAT(vinsertados, ' festivo(s) insertado(s) para el año ', wanio, '.');
END //

DELIMITER ;