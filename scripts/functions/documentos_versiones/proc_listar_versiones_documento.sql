DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_versiones_documento(
    IN wid_documento INT
)
BEGIN
    SELECT
        id_version,
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
        motivo_rechazo,
        id_aprobador,
        fec_resolucion,
        usr_insert,
        fec_insert
    FROM t_documentos_versiones
    WHERE id_documento = wid_documento
      AND fec_delete IS NULL
    ORDER BY CASE WHEN numero_version IS NULL THEN 0 ELSE numero_version END DESC,
             id_version DESC;
END //

DELIMITER ;
