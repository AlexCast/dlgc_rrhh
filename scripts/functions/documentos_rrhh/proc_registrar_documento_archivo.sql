DELIMITER //

-- Procedimiento transaccional: registra un documento del empleado en sesión
-- (módulo Documentación 31) junto con su primera versión (archivo adjunto).
-- Sustituye a la combinación fun_insert_documentos_rrhh + LAST_INSERT_ID()
-- (que en MariaDB no propaga el id fuera de la función) + proc_subir_version_documento.
CREATE OR REPLACE PROCEDURE proc_registrar_documento_archivo(
    IN wid_usuario VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    IN wid_tipo_documento INT,
    IN wid_contrato INT,
    IN wtitulo VARCHAR(150) COLLATE utf8mb4_unicode_ci,
    IN wproveedor VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    IN wstorage_key VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    IN wnombre_original VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    IN wmime_type VARCHAR(100) COLLATE utf8mb4_unicode_ci,
    IN wtamano_bytes INT,
    IN whash_sha256 CHAR(64) COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_documento INT;
    DECLARE v_empleado INT DEFAULT 0;
    DECLARE v_tipo INT DEFAULT 0;
    DECLARE v_contrato INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT NULL AS id_documento, 'Ocurrió un error al registrar el documento.' AS resultado;
    END;

    -- Validaciones previas (antes de la transacción).
    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        SELECT NULL AS id_documento, 'El empleado propietario del documento es obligatorio.' AS resultado;
    ELSEIF wid_tipo_documento IS NULL OR wid_tipo_documento <= 0 THEN
        SELECT NULL AS id_documento, 'El tipo de documento es obligatorio.' AS resultado;
    ELSEIF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 3 THEN
        SELECT NULL AS id_documento, 'El título del documento debe tener al menos 3 caracteres.' AS resultado;
    ELSEIF wstorage_key IS NULL OR LENGTH(TRIM(wstorage_key)) < 1 OR LENGTH(TRIM(wstorage_key)) > 255 THEN
        SELECT NULL AS id_documento, 'La referencia de almacenamiento del archivo no es válida.' AS resultado;
    ELSEIF wnombre_original IS NULL OR LENGTH(TRIM(wnombre_original)) < 1 OR LENGTH(TRIM(wnombre_original)) > 255 THEN
        SELECT NULL AS id_documento, 'El nombre del archivo no es válido.' AS resultado;
    ELSEIF wmime_type NOT IN ('application/pdf', 'image/jpeg', 'image/png', 'image/webp') THEN
        SELECT NULL AS id_documento, 'Solo se permiten archivos PDF, JPG, PNG o WebP.' AS resultado;
    ELSEIF wtamano_bytes IS NULL OR wtamano_bytes <= 0 OR wtamano_bytes > 10485760 THEN
        SELECT NULL AS id_documento, 'El archivo debe pesar entre 1 byte y 10 MB.' AS resultado;
    ELSEIF whash_sha256 IS NULL OR LENGTH(whash_sha256) <> 64 THEN
        SELECT NULL AS id_documento, 'El hash de integridad del archivo no es válido.' AS resultado;
    ELSE
        -- El dueño del documento debe ser un empleado activo (FK a t_empleados).
        SELECT COUNT(*) INTO v_empleado
        FROM t_empleados
        WHERE id_usuario = wid_usuario
          AND fec_delete IS NULL;

        IF v_empleado = 0 THEN
            SELECT NULL AS id_documento, 'El usuario no está registrado como empleado activo y no puede registrar documentos.' AS resultado;
        ELSE
            SELECT COUNT(*) INTO v_tipo
            FROM t_tipos_documento_rrhh
            WHERE id_tipo_documento = wid_tipo_documento
              AND fec_delete IS NULL;

            IF v_tipo = 0 THEN
                SELECT NULL AS id_documento, 'El tipo de documento indicado no existe.' AS resultado;
            ELSE
                -- Si se asocia un contrato, debe pertenecer al mismo empleado.
                IF wid_contrato IS NOT NULL THEN
                    SELECT COUNT(*) INTO v_contrato
                    FROM t_contratos_empleados
                    WHERE id_contrato = wid_contrato
                      AND id_usuario = wid_usuario
                      AND fec_delete IS NULL;

                    IF v_contrato = 0 THEN
                        SELECT NULL AS id_documento, 'El contrato indicado no pertenece al empleado o no está vigente.' AS resultado;
                    ELSE
                        SET v_contrato = 1;
                    END IF;
                END IF;

                IF wid_contrato IS NULL OR v_contrato = 1 THEN
                    START TRANSACTION;

                    INSERT INTO t_documentos_rrhh (
                        id_usuario, id_tipo_documento, id_contrato, titulo, estado,
                        usr_insert, fec_insert
                    ) VALUES (
                        TRIM(wid_usuario), wid_tipo_documento, wid_contrato, TRIM(wtitulo), 'ACTIVO',
                        COALESCE(@app_current_user, CURRENT_USER()), CURRENT_TIMESTAMP
                    );

                    SET v_documento = LAST_INSERT_ID();

                    INSERT INTO t_documentos_versiones (
                        id_documento, numero_version, proveedor, storage_key, nombre_original,
                        mime_type, tamano_bytes, hash_sha256, es_actual, observacion,
                        estado_aprobacion, id_aprobador, fec_resolucion, usr_insert, fec_insert
                    ) VALUES (
                        v_documento, 1, UPPER(TRIM(wproveedor)), TRIM(wstorage_key), TRIM(wnombre_original),
                        wmime_type, wtamano_bytes, whash_sha256, TRUE, NULL,
                        'APROBADO', TRIM(wid_usuario), CURRENT_TIMESTAMP,
                        COALESCE(@app_current_user, CURRENT_USER()), CURRENT_TIMESTAMP
                    );

                    COMMIT;

                    SELECT v_documento AS id_documento, 'Documento registrado correctamente con su archivo.' AS resultado;
                END IF;
            END IF;
        END IF;
    END IF;
END //

DELIMITER ;
