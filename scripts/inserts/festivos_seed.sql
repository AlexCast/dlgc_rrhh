-- ============================================================
-- Seed: Festivos nacionales de Colombia
-- Ejecutar DESPUÉS de rebuild_functions.ps1 (requiere fun_sembrar_festivos_colombia)
-- Idempotente: ignora fechas que ya existan en t_dias_festivos
-- ============================================================

-- Festivos 2025
SELECT fun_sembrar_festivos_colombia(2025);

-- Festivos 2026
SELECT fun_sembrar_festivos_colombia(2026);

-- Festivos 2027
SELECT fun_sembrar_festivos_colombia(2027);
