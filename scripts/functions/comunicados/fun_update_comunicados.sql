DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_comunicados(
    wid_comunicado INT,
    wtitulo VARCHAR(100),
    wcontenido TEXT,
    wcategoria VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'La categoría indicada no es válida o ocurrió un error al actualizar.';

    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RETURN 'El ID del comunicado no es válido.';
    END IF;

    IF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 5 THEN
        RETURN 'El título del comunicado debe tener al menos 5 caracteres.';
    END IF;

    IF wcontenido IS NULL OR LENGTH(TRIM(wcontenido)) < 10 THEN
        RETURN 'El contenido del comunicado debe tener al menos 10 caracteres.';
    END IF;

    IF wcategoria IS NULL OR TRIM(wcategoria) = '' THEN
        RETURN 'La categoría del comunicado es obligatoria.';
    END IF;

    UPDATE t_comunicados
    SET titulo = TRIM(wtitulo),
        contenido = TRIM(wcontenido),
        categoria = UPPER(TRIM(wcategoria)),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Comunicado actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el comunicado activo para actualizar.';
END //

DELIMITER ;