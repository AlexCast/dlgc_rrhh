DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_documentos_gestor()
BEGIN
    SELECT
        d.id_documento,
        d.id_usuario,
        CONCAT_WS(
            ' ',
            NULLIF(TRIM(u.primer_nombre), ''),
            NULLIF(TRIM(u.segundo_nombre), ''),
            NULLIF(TRIM(u.primer_apellido), ''),
            NULLIF(TRIM(u.segundo_apellido), '')
        ) AS nombre_empleado,
        t.codigo AS tipo_codigo,
        t.nombre AS tipo_documento,
        d.titulo,
        d.estado,
        d.fec_insert AS documento_fecha,
        v.id_version,
        v.numero_version,
        v.nombre_original AS archivo_actual,
        v.mime_type AS archivo_mime,
        v.tamano_bytes AS archivo_tamano,
        v.hash_sha256 AS archivo_hash,
        v.observacion AS archivo_observacion,
        v.fec_insert AS archivo_fecha,
        (
            SELECT COUNT(*)
            FROM t_documentos_versiones x
            WHERE x.id_documento = d.id_documento
              AND x.fec_delete IS NULL
        ) AS total_versiones
    FROM t_documentos_rrhh d
    INNER JOIN t_tipos_documento_rrhh t
        ON t.id_tipo_documento = d.id_tipo_documento
    LEFT JOIN t_usuarios u
        ON u.id_usuario = d.id_usuario
    LEFT JOIN t_documentos_versiones v
        ON v.id_documento = d.id_documento
       AND v.es_actual = 1
       AND v.fec_delete IS NULL
    WHERE d.fec_delete IS NULL
    ORDER BY d.fec_insert DESC, d.id_documento DESC;
END //

DELIMITER ;
