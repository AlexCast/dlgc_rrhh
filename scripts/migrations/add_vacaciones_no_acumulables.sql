-- ============================================================
-- Migración: política de vacaciones NO acumulables (regla propia de la empresa,
-- más estricta que el mínimo legal). Reemplaza el enfoque de acumulación de
-- add_vacaciones_saldo.sql: cada aniversario de fecha_ingreso reinicia el
-- contador a 15 días hábiles; lo no disfrutado del ciclo anterior se pierde.
-- Aplicar sobre una base de datos VIVA. Ejecutar dentro de una transacción.
-- Requiere haber aplicado antes add_vacaciones_ajustes.sql.
-- ============================================================

BEGIN;

-- Ya no aplica: la empresa no permite acumulación de ningún tipo (ni 2 ni 4 años).
ALTER TABLE t_empleados
    DROP COLUMN IF EXISTS tope_acumulacion_anios;

-- Ancla el ajuste manual al ciclo aniversario al que corresponde (para que deje de
-- contar automáticamente cuando ese ciclo se reinicia), independiente de fec_insert
-- (que es la auditoría de cuándo se cargó el registro, no de qué ciclo habla).
ALTER TABLE t_vacaciones_ajustes
    ADD COLUMN IF NOT EXISTS fecha_ajuste DATE NOT NULL DEFAULT CURRENT_DATE;

COMMIT;
