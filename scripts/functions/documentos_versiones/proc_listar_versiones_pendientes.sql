DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_versiones_pendientes()
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
        v.observacion,
        v.estado_aprobacion,
        v.fec_insert,
        d.id_usuario,
        d.titulo,
        t.codigo AS tipo_codigo,
        t.nombre AS tipo_documento,
        CONCAT_WS(
            ' ',
            NULLIF(TRIM(u.primer_nombre), ''),
            NULLIF(TRIM(u.segundo_nombre), ''),
            NULLIF(TRIM(u.primer_apellido), ''),
            NULLIF(TRIM(u.segundo_apellido), '')
        ) AS nombre_empleado
    FROM t_documentos_versiones v
    INNER JOIN t_documentos_rrhh d
        ON d.id_documento = v.id_documento
       AND d.fec_delete IS NULL
    INNER JOIN t_tipos_documento_rrhh t
        ON t.id_tipo_documento = d.id_tipo_documento
    LEFT JOIN t_usuarios u
        ON u.id_usuario = d.id_usuario
    WHERE v.estado_aprobacion = 'PENDIENTE'
      AND v.fec_delete IS NULL
    ORDER BY v.fec_insert ASC, v.id_version ASC;
END //

DELIMITER ;
