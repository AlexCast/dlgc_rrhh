-- ============================================================
-- documentos_seeds.sql — Módulos Documentación (31) y Gestor
-- de Archivos (30): almacenamiento LOCAL y catálogo de tipos.
--
-- Idempotente: se puede ejecutar cuantas veces se necesite.
-- Ejecutar después de modelo_mariadb.sql y rbac_seeds_mariadb.sql.
-- ============================================================

SET @app_current_user = 'seed_documentos';

-- 1. Proveedor de almacenamiento LOCAL.
--    El gestor guarda los archivos en uploads/documentos/ (disco del servidor)
--    y persiste la referencia (proveedor='LOCAL', storage_key=ruta relativa).
--    Para bases existentes esto replica el CHECK ampliado del modelo.
ALTER TABLE t_documentos_versiones
    MODIFY COLUMN proveedor VARCHAR(20) NOT NULL DEFAULT 'LOCAL'
    CHECK (proveedor IN ('LOCAL', 'R2'));

-- 2. Catálogo de tipos de documento (adicional a los 4 sembrados en el modelo).
INSERT INTO t_tipos_documento_rrhh (codigo, nombre, usr_insert, fec_insert)
SELECT t.codigo, t.nombre, t.usr_insert, t.fec_insert
FROM (
    SELECT 'CERTIFICADO_LABORAL'       AS codigo, 'Certificado laboral'           AS nombre, 'seed' AS usr_insert, CURRENT_TIMESTAMP AS fec_insert
    UNION ALL SELECT 'OTROSI',              'Otrosí de contrato',                   'seed', CURRENT_TIMESTAMP
    UNION ALL SELECT 'AUTORIZACION_AFILIACION', 'Autorización de afiliación',      'seed', CURRENT_TIo obligatorio de accidentes de tránsito)', 'seed', CURRENT_TIMESTAMP
    UNION ALL SELECT 'CERTIFICADO_BANCARIO', 'Certificación bancaria',             'seed', CURRENT_TIMESTAMPMESTAMP
    UNION ALL SELECT 'SOAT',                  'SOAT (segur
    UNION ALL SELECT 'CERTIFICADO_PENSION',   'Certificado de pensión',            'seed', CURRENT_TIMESTAMP
    UNION ALL SELECT 'CERTIFICADO_CAJA',      'Certificado de caja de compensación', 'seed', CURRENT_TIMESTAMP
    UNION ALL SELECT 'OTRO',                  'Otro documento',                    'seed', CURRENT_TIMESTAMP
) AS t
WHERE NOT EXISTS (
    SELECT 1 FROM t_tipos_documento_rrhh x WHERE x.codigo = t.codigo
);
