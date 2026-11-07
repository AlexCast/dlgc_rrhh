DELIMITER //

CREATE OR REPLACE PROCEDURE proc_obtener_version_archivo(
    IN wid_version INT
)
BEGIN
    SELECT
        v.id_version,
        v.id_documento,
        v.proveedor,
        v.storage_key,
        v.nombre_original,
        v.mime_type,
        v.tamano_bytes,
        v.hash_sha256,
        v.numero_version,
        v.es_actual,
        v.fec_insert
    FROM t_documentos_versiones v
    WHERE v.id_version = wid_version
      AND v.fec_delete IS NULL;
END //

DELIMITER ;
