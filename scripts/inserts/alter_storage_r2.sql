-- ============================================================
-- alter_storage_r2.sql
-- Migración idempotente: Cloudflare R2 reemplaza a Bunny como
-- proveedor de almacenamiento de objetos.
--
-- Cambios:
--   A) t_permisos_evidencias.proveedor:
--      DEFAULT 'R2' y CHECK ('R2', 'NULL').
--   B) t_documentos_versiones.proveedor:
--      CHECK ('LOCAL', 'R2').
--
-- Las filas existentes con proveedor 'BUNNY' no se migran: el CHECK
-- solo afecta inserciones/actualizaciones futuras. Aplicar con el
-- cliente de MariaDB o copiando las sentencias en el servidor.
-- ============================================================

-- A) Evidencias de solicitudes de permiso
ALTER TABLE t_permisos_evidencias
    MODIFY COLUMN proveedor VARCHAR(20) NOT NULL DEFAULT 'R2'
    CHECK (proveedor IN ('R2', 'NULL'));

-- B) Versiones del gestor de documentación
ALTER TABLE t_documentos_versiones
    MODIFY COLUMN proveedor VARCHAR(20) NOT NULL DEFAULT 'LOCAL'
    CHECK (proveedor IN ('LOCAL', 'R2'));
