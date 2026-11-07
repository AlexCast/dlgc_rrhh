DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_documentos_usuario(
    IN wid_usuario VARCHAR(20) COLLATE utf8mb4_unicode_ci
)
BEGIN
    SELECT
        d.id_documento,
        d.id_tipo_documento,
        t.codigo AS tipo_codigo,
        t.nombre AS tipo_documento,
        d.id_contrato,
        d.titulo,
        d.estado,
        d.fec_insert AS documento_fecha,
        v.id_version,
        v.numero_version,
        v.nombre_original AS archivo_actual,
        v.mime_type AS archivo_mime,
        v.tamano_bytes AS archivo_tamano,
        v.fec_insert AS archivo_fecha
    FROM t_documentos_rrhh d
    INNER JOIN t_tipos_documento_rrhh t
        ON t.id_tipo_documento = d.id_tipo_documento
    LEFT JOIN t_documentos_versiones v
        ON v.id_documento = d.id_documento
       AND v.es_actual = 1
       AND v.fec_delete IS NULL
    WHERE d.id_usuario = wid_usuario
      AND d.fec_delete IS NULL
    ORDER BY d.fec_insert DESC, d.id_documento DESC;
END //

DELIMITER ;
