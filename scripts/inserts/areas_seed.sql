-- ============================================================
-- Seed: Áreas de la empresa
-- Fuente: organigrama de templates/index.html
-- Ejecutar después de crear el esquema (modelo_mariadb.sql)
-- Idempotente: no inserta áreas cuyo nombre ya exista
-- ============================================================

-- ------------------------------------------------------------
-- Áreas (16 del organigrama)
-- ------------------------------------------------------------
INSERT INTO t_areas (nombre_area, usr_insert, fec_insert)
SELECT a.nombre_area, 'seed', CURRENT_TIMESTAMP
FROM (
    SELECT 'Dirección General' AS nombre_area
    UNION ALL SELECT 'Directora de ventas'
    UNION ALL SELECT 'Jefe de ventas'
    UNION ALL SELECT 'Supervisora de ventas'
    UNION ALL SELECT 'Compras'
    UNION ALL SELECT 'Vendedoras'
    UNION ALL SELECT 'Telemercadeo'
    UNION ALL SELECT 'Directora Administrativa'
    UNION ALL SELECT 'Aux. contable'
    UNION ALL SELECT 'Aux. de cartera - Cobros'
    UNION ALL SELECT 'Aux. de cartera - Pedidos'
    UNION ALL SELECT 'Directora de logística'
    UNION ALL SELECT 'Supervisora de belleza'
    UNION ALL SELECT 'Supervisora de empaques'
    UNION ALL SELECT 'Supervisora de papeleria'
    UNION ALL SELECT 'Auxiliares de logística'
) AS a
WHERE NOT EXISTS (
    SELECT 1
    FROM t_areas x
    WHERE x.nombre_area = a.nombre_area
);

-- Verificación: deben listarse 16 áreas
-- SELECT id_area, nombre_area FROM t_areas WHERE fec_delete IS NULL ORDER BY nombre_area;