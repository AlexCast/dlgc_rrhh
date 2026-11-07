-- ============================================================
-- alter_intentos_subida_mi_ficha.sql
-- Migración idempotente: habilita el tipo 'mi_ficha' en el
-- limitador de ediciones autoservicio de la ficha técnica.
--
-- Cambios:
--   A) t_intentos_subida.tipo:
--      CHECK ampliado para aceptar 'mi_ficha'.
--
-- Las filas existentes no se ven afectadas: el CHECK solo valida
-- inserciones/actualizaciones futuras. Aplicar con el cliente de
-- MariaDB o copiando las sentencias en el servidor.
-- ============================================================

ALTER TABLE t_intentos_subida
    MODIFY COLUMN tipo VARCHAR(40) NOT NULL
    CHECK (tipo IN ('foto_perfil', 'evidencias', 'gestor_subida', 'documento_subida', 'mi_ficha'));
