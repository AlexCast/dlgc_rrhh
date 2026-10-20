-- ============================================================
-- Migración: los ajustes de vacaciones solo pueden restar días.
-- Por ley el ciclo causa exactamente 15 días; el ajuste manual solo tiene sentido
-- para reconocer días ya disfrutados históricamente (migración), nunca para sumar
-- días extra. NOT VALID: no revalida filas históricas ya anuladas (fec_delete no nulo).
-- Aplicar sobre una base de datos VIVA. Ejecutar dentro de una transacción.
-- ============================================================

BEGIN;

ALTER TABLE t_vacaciones_ajustes
    DROP CONSTRAINT IF EXISTS t_vacaciones_ajustes_dias_ajuste_check;

ALTER TABLE t_vacaciones_ajustes
    ADD CONSTRAINT chk_vacaciones_ajustes_dias_negativo CHECK (dias_ajuste < 0) NOT VALID;

COMMIT;
