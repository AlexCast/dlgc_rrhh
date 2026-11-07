DELIMITER //

-- Procedimiento transaccional: sube una nueva versión APROBADA de un documento.
-- MariaDB no permite COMMIT/ROLLBACK dentro de funciones; por eso es un PROCEDIMIENTO
-- que devuelve el mensaje de resultado como fila (columna "resultado").
CREATE OR REPLACE PROCEDURE proc_subir_version_documento(
    IN wid_documento INT,
    IN wproveedor VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    IN wstorage_key VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    IN wnombre_original VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    IN wmime_type VARCHAR(100) COLLATE utf8mb4_unicode_ci,
    IN wtamano_bytes INT,
    IN whash_sha256 CHAR(64) COLLATE utf8mb4_unicode_ci,
    IN wobservacion VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    IN wid_aprobador VARCHAR(20) COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_mensaje VARCHAR(255) DEFAULT '';
    DECLARE v_documento INT;
    DECLARE v_duplicado INT DEFAULT 0;
    DECLARE v_numero INT DEFAULT 1;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_documento = NULL;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'No se pudo registrar la versión: verifique los datos del archivo y del documento.' AS resultado;
    END;

    logica: BEGIN
        -- Validaciones previas (antes de abrir la transacción).
        IF wid_documento IS NULL OR wid_documento <= 0 THEN
            SET v_mensaje = 'El documento es obligatorio.';
            LEAVE logica;
        END IF;

        IF wstorage_key IS NULL OR LENGTH(TRIM(wstorage_key)) < 1 OR LENGTH(TRIM(wstorage_key)) > 255 THEN
            SET v_mensaje = 'La referencia de almacenamiento del archivo no es válida.';
            LEAVE logica;
        END IF;

        IF wnombre_original IS NULL OR LENGTH(TRIM(wnombre_original)) < 1 OR LENGTH(TRIM(wnombre_original)) > 255 THEN
            SET v_mensaje = 'El nombre del archivo no es válido.';
            LEAVE logica;
        END IF;

        IF wmime_type NOT IN ('application/pdf', 'image/jpeg', 'image/png', 'image/webp') THEN
            SET v_mensaje = 'Solo se permiten archivos PDF, JPG, PNG o WebP.';
            LEAVE logica;
        END IF;

        IF wtamano_bytes IS NULL OR wtamano_bytes <= 0 OR wtamano_bytes > 10485760 THEN
            SET v_mensaje = 'El archivo debe pesar entre 1 byte y 10 MB.';
            LEAVE logica;
        END IF;

        IF whash_sha256 IS NULL OR LENGTH(whash_sha256) <> 64 THEN
            SET v_mensaje = 'El hash de integridad del archivo no es válido.';
            LEAVE logica;
        END IF;

        IF wid_aprobador IS NULL OR TRIM(wid_aprobador) = '' THEN
            SET v_mensaje = 'El usuario aprobador es obligatorio.';
            LEAVE logica;
        END IF;

        START TRANSACTION;

        -- Serializa la numeración de versiones por documento.
        SELECT id_documento INTO v_documento
        FROM t_documentos_rrhh
        WHERE id_documento = wid_documento
          AND fec_delete IS NULL
        FOR UPDATE;

        IF v_documento IS NULL THEN
            ROLLBACK;
            SET v_mensaje = 'El documento no existe o fue eliminado.';
            LEAVE logica;
        END IF;

        -- Detección de duplicados: el mismo contenido ya fue subido para este documento.
        SELECT COUNT(*) INTO v_duplicado
        FROM t_documentos_versiones
        WHERE id_documento = wid_documento
          AND hash_sha256 = whash_sha256
          AND fec_delete IS NULL;

        IF v_duplicado > 0 THEN
            ROLLBACK;
            SET v_mensaje = 'Ya existe una versión con el mismo contenido para este documento.';
            LEAVE logica;
        END IF;

        -- La versión vigente anterior deja de serlo.
        UPDATE t_documentos_versiones
        SET es_actual = 0
        WHERE id_documento = wid_documento
          AND es_actual = 1
          AND fec_delete IS NULL;

        -- La nueva versión se numera y queda vigente (aprobada en el momento de la subida).
        SELECT COALESCE(MAX(numero_version), 0) + 1 INTO v_numero
        FROM t_documentos_versiones
        WHERE id_documento = wid_documento
          AND fec_delete IS NULL;

        INSERT INTO t_documentos_versiones (
            id_documento,
            numero_version,
            proveedor,
            storage_key,
            nombre_original,
            mime_type,
            tamano_bytes,
            hash_sha256,
            es_actual,
            observacion,
            estado_aprobacion,
            id_aprobador,
            fec_resolucion,
            usr_insert,
            fec_insert
        )
        VALUES (
            wid_documento,
            v_numero,
            UPPER(TRIM(wproveedor)),
            TRIM(wstorage_key),
            TRIM(wnombre_original),
            wmime_type,
            wtamano_bytes,
            whash_sha256,
            TRUE,
            NULLIF(TRIM(wobservacion), ''),
            'APROBADO',
            TRIM(wid_aprobador),
            CURRENT_TIMESTAMP,
            COALESCE(@app_current_user, CURRENT_USER()),
            CURRENT_TIMESTAMP
        );

        COMMIT;

        SET v_mensaje = CONCAT('Versión ', v_numero, ' subida correctamente.');
    END logica;

    SELECT v_mensaje AS resultado;
END //

DELIMITER ;
