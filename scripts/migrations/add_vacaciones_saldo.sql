-- ============================================================
-- Migración: Control de saldo de vacaciones (CST Art. 186 y sig.)
-- No se crea tabla física de saldo: el saldo se calcula dinámicamente
-- con fun_calcular_saldo_vacaciones() a partir de fecha_ingreso y de
-- los permisos APROBADOS con metodo_descuento = 'VACACIONES'.
-- Aplicar sobre una base de datos VIVA. Ejecutar dentro de una transacción.
--
-- [SUPERADO 2026-08-05] La columna tope_acumulacion_anios que se agregaba aquí
-- asumía acumulación permitida (2/4 años). Esta empresa NO permite acumular:
-- las vacaciones se reinician cada aniversario de fecha_ingreso y lo no
-- disfrutado se pierde. Ver scripts/migrations/add_vacaciones_no_acumulables.sql,
-- que elimina esa columna. Este archivo se conserva como registro histórico.
-- ============================================================

BEGIN;

-- tope_acumulacion_anios: 2 años para empleados normales, 4 años para cargos
-- de dirección/confianza/manejo (Art. 190 CST). Por defecto 2; RRHH debe marcar
-- manualmente a 4 los empleados que apliquen (hoy no hay campo de "cargo de
-- confianza" en el sistema, así que se administra caso a caso desde este campo).
ALTER TABLE t_empleados
    ADD COLUMN IF NOT EXISTS tope_acumulacion_anios SMALLINT NOT NULL DEFAULT 2
    CHECK (tope_acumulacion_anios IN (2, 4));

COMMIT;

