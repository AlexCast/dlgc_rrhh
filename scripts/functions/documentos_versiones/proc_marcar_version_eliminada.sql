DELIMITER //

CREATE OR REPLACE PROCEDURE proc_marcar_version_eliminada(
    IN wid_version INT,
    IN wid_actor VARCHAR(20) COLLATE utf8mb4_unicode_ci
)
BEGIN
    UPDATE t_documentos_versiones
       SET fec_delete = CURRENT_TIMESTAMP,
           usr_delete = COALESCE(NULLIF(TRIM(wid_actor), ''), CURRENT_USER())
     WHERE id_version = wid_version
       AND estado_aprobacion = 'RECHAZADO'
       AND fec_delete IS NULL;

    SELECT ROW_COUNT() AS eliminada;
END //

DELIMITER ;
