DELIMITER //

CREATE OR REPLACE PROCEDURE proc_rechazar_version_documento(
    IN wid_version INT,
    IN wid_aprobador VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    IN wmotivo VARCHAR(255) COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_estado VARCHAR(20);
    DECLARE v_mensaje VARCHAR(255) DEFAULT '';

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'No se pudo rechazar la versión.' AS resultado;
    END;

    IF wid_version IS NULL OR wid_version <= 0 THEN
        SET v_mensaje = 'La versión indicada no es válida.';
    ELSEIF wid_aprobador IS NULL OR TRIM(wid_aprobador) = '' THEN
        SET v_mensaje = 'El usuario revisor es obligatorio.';
    ELSEIF wmotivo IS NULL OR CHAR_LENGTH(TRIM(wmotivo)) < 3 THEN
        SET v_mensaje = 'El motivo del rechazo es obligatorio.';
    ELSE
        START TRANSACTION;

        SELECT estado_aprobacion INTO v_estado
          FROM t_documentos_versiones
         WHERE id_version = wid_version
           AND fec_delete IS NULL
         FOR UPDATE;

        IF v_estado IS NULL THEN
            SET v_mensaje = 'La versión no existe o fue eliminada.';
        ELSEIF v_estado <> 'PENDIENTE' THEN
            SET v_mensaje = 'Solo se pueden rechazar versiones pendientes.';
        ELSE
            UPDATE t_documentos_versiones
               SET estado_aprobacion = 'RECHAZADO',
                   es_actual = 0,
                   numero_version = NULL,
                   id_aprobador = TRIM(wid_aprobador),
                   fec_resolucion = CURRENT_TIMESTAMP,
                   motivo_rechazo = LEFT(TRIM(wmotivo), 255)
             WHERE id_version = wid_version;

            SET v_mensaje = 'Versión rechazada correctamente.';
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
