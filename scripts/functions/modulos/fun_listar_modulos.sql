DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_modulos(
    wid_modulo INT
) RETURNS BOOLEAN
BEGIN
    -- Declaración estricta de variables individuales en lugar de %ROWTYPE
    DECLARE v_id_modulo INT;
    DECLARE v_nombre_modulo VARCHAR(30);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    -- El reemplazo directo del IF NOT FOUND de PostgreSQL
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN FALSE;
    END IF;

    -- Eliminamos el SELECT *; apuntamos a columnas específicas
    SELECT id_modulo, nombre_modulo
    INTO v_id_modulo, v_nombre_modulo
    FROM t_modulos
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    -- En PostgreSQL usabas RAISE NOTICE para imprimir los datos.
    -- En MariaDB retornamos TRUE indicando que la función fue exitosa,
    -- la lectura de los datos se haría mediante un procedimiento almacenado
    -- si se requiere devolver el set de resultados al backend.
    RETURN TRUE;
END //

DELIMITER ;