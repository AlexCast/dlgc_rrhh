DELIMITER //

CREATE OR REPLACE PROCEDURE proc_aprobar_version_documento(
    IN wid_version INT,
    IN wid_aprobador VARCHAR(20) COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_id_documento INT DEFAULT NULL;
    DECLARE v_documento_existe INT DEFAULT NULL;
    DECLARE v_estado VARCHAR(20);
    DECLARE v_numero INT DEFAULT 1;
    DECLARE v_mensaje VARCHAR(255) DEFAULT '';

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'No se pudo aprobar la versión.' AS resultado;
    END;

    IF wid_version IS NULL OR wid_version <= 0 THEN
        SET v_mensaje = 'La versión indicada no es válida.';
    ELSEIF wid_aprobador IS NULL OR TRIM(wid_aprobador) = '' THEN
        SET v_mensaje = 'El usuario aprobador es obligatorio.';
    ELSE
        START TRANSACTION;

        SELECT id_documento, estado_aprobacion
          INTO v_id_documento, v_estado
          FROM t_documentos_versiones
         WHERE id_version = wid_version
           AND fec_delete IS NULL
         FOR UPDATE;

        IF v_id_documento IS NULL THEN
            SET v_mensaje = 'La versión no existe o fue eliminada.';
        ELSEIF v_estado <> 'PENDIENTE' THEN
            SET v_mensaje = 'Solo se pueden aprobar versiones pendientes.';
        ELSE
            SELECT id_documento INTO v_documento_existe
              FROM t_documentos_rrhh
             WHERE id_documento = v_id_documento
               AND fec_delete IS NULL
             FOR UPDATE;

            IF v_documento_existe IS NULL THEN
                SET v_mensaje = 'El documento no existe o fue eliminado.';
            ELSE
                UPDATE t_documentos_versiones
                   SET es_actual = 0
                 WHERE id_documento = v_id_documento
                   AND es_actual = 1
                   AND fec_delete IS NULL;

                SELECT COALESCE(MAX(numero_version), 0) + 1 INTO v_numero
                  FROM t_documentos_versiones
                 WHERE id_documento = v_id_documento
                   AND fec_delete IS NULL;

                UPDATE t_documentos_versiones
                   SET numero_version = v_numero,
                       es_actual = 1,
                       estado_aprobacion = 'APROBADO',
                       id_aprobador = TRIM(wid_aprobador),
                       fec_resolucion = CURRENT_TIMESTAMP,
                       motivo_rechazo = NULL
                 WHERE id_version = wid_version;

                SET v_mensaje = CONCAT('Versión ', v_numero, ' aprobada correctamente.');
            END IF;
        END IF;

        IF v_mensaje LIKE '%correctamente%' THEN
            COMMIT;
        ELSE
            ROLLBACK;
        END IF;
    END IF;

    SELECT v_mensaje AS resultado;
END //

DELIMITER ;
