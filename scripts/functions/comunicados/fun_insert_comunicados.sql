DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_comunicados(
    wtitulo VARCHAR(100),
    wcontenido TEXT,
    wcategoria VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    -- En MariaDB los errores de CHECK (como la categoría no permitida) lanzan una excepción general
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'La categoría indicada no es válida o ocurrió un error al insertar.';

    IF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 5 THEN
        RETURN 'El título del comunicado debe tener al menos 5 caracteres.';
    END IF;

    IF wcontenido IS NULL OR LENGTH(TRIM(wcontenido)) < 10 THEN
        RETURN 'El contenido del comunicado debe tener al menos 10 caracteres.';
    END IF;

    IF wcategoria IS NULL OR TRIM(wcategoria) = '' THEN
        RETURN 'La categoría del comunicado es obligatoria.';
    END IF;

    INSERT INTO t_comunicados (
        titulo,
        contenido,
        categoria,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wtitulo),
        TRIM(wcontenido),
        UPPER(TRIM(wcategoria)),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Comunicado insertado correctamente.';
END //

DELIMITER ;