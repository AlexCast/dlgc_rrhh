DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_documentos_rrhh(
    wid_usuario VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    wid_tipo_documento INT,
    wid_contrato INT,
    wtitulo VARCHAR(150) COLLATE utf8mb4_unicode_ci
) RETURNS VARCHAR(255)
BEGIN
    DECLARE v_empleado INT DEFAULT 0;
    DECLARE v_tipo INT DEFAULT 0;
    DECLARE v_contrato INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al registrar el documento.';

    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        RETURN 'El empleado propietario del documento es obligatorio.';
    END IF;

    IF wid_tipo_documento IS NULL OR wid_tipo_documento <= 0 THEN
        RETURN 'El tipo de documento es obligatorio.';
    END IF;

    IF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 3 THEN
        RETURN 'El título del documento debe tener al menos 3 caracteres.';
    END IF;

    -- El dueño del documento debe ser un empleado activo (FK a t_empleados).
    SELECT COUNT(*) INTO v_empleado
    FROM t_empleados
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF v_empleado = 0 THEN
        RETURN 'El usuario no está registrado como empleado activo y no puede registrar documentos.';
    END IF;

    SELECT COUNT(*) INTO v_tipo
    FROM t_tipos_documento_rrhh
    WHERE id_tipo_documento = wid_tipo_documento
      AND fec_delete IS NULL;

    IF v_tipo = 0 THEN
        RETURN 'El tipo de documento indicado no existe.';
    END IF;

    -- Si se asocia un contrato, debe pertenecer al mismo empleado.
    IF wid_contrato IS NOT NULL THEN
        SELECT COUNT(*) INTO v_contrato
        FROM t_contratos_empleados
        WHERE id_contrato = wid_contrato
          AND id_usuario = wid_usuario
          AND fec_delete IS NULL;

        IF v_contrato = 0 THEN
            RETURN 'El contrato indicado no pertenece al empleado o no está vigente.';
        END IF;
    END IF;

    INSERT INTO t_documentos_rrhh (
        id_usuario,
        id_tipo_documento,
        id_contrato,
        titulo,
        estado,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wid_usuario),
        wid_tipo_documento,
        wid_contrato,
        TRIM(wtitulo),
        'ACTIVO',
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Documento registrado correctamente.';
END //

DELIMITER ;
