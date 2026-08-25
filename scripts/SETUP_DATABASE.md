# Setup de Base de Datos — DLGC RRHH

Orden de ejecución para una base de datos **nueva** o **vacía**.

## 1. Esquema base
```bash
psql -U postgres -d db_dlgc_rrhh -f scripts/modelo_db.sql
```

## 2. Funciones almacenadas
```powershell
# Desde PowerShell (o adaptar a bash si es necesario)
.\scripts\rebuild_functions.ps1 -PgPassword "tu_clave"
```

## 3. Datos iniciales (seeds)
```bash
# Datos geográficos y de instituciones
psql -U postgres -d db_dlgc_rrhh -f scripts/inserts/dpts_municipios.sql
psql -U postgres -d db_dlgc_rrhh -f scripts/inserts/institutions_insert.sql

# RBAC (roles, operaciones, permisos)
psql -U postgres -d db_dlgc_rrhh -f scripts/inserts/rbac_seeds.sql

# Usuario administrador inicial
psql -U postgres -d db_dlgc_rrhh -f scripts/inserts/user_insert.sql

# Festivos nacionales (Colombia) — años 2025-2027
# REQUIERE el paso 2 (usa fun_sembrar_festivos_colombia). Idempotente.
# Cada diciembre: agregar el año siguiente al archivo o ejecutar
# SELECT fun_sembrar_festivos_colombia(<anio>);
psql -U postgres -d db_dlgc_rrhh -f scripts/inserts/festivos_seed.sql
```

> Los festivos de **empresa** (tipo EMPRESA) no van por seed: se administran
> desde la aplicación, módulo Días Festivos.

## 4. Triggers
```bash
psql -U postgres -d db_dlgc_rrhh -f scripts/triggers/auditorias.sql
psql -U postgres -d db_dlgc_rrhh -f scripts/triggers/softdelete.sql
```

## Verificación rápida
```sql
-- ¿Hay festivos?
SELECT COUNT(*) FROM t_dias_festivos WHERE fec_delete IS NULL;

-- ¿Funciona el endpoint?
SELECT * FROM t_dias_festivos WHERE EXTRACT(YEAR FROM fecha) = 2026 LIMIT 5;
```

## Nota sobre migraciones
Si la DB ya existía con datos antes de este setup, aplica las migraciones puntuales desde `scripts/migrations/` **en lugar de** `modelo_db.sql`.
