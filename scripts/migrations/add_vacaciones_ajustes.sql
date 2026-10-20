-- ============================================================
-- Migración: ledger de ajustes manuales de saldo de vacaciones.
-- Necesario para el salto a producción: reconoce días ya disfrutados
-- (o créditos especiales) que NO quedaron registrados como solicitudes
-- en este sistema (histórico previo en papel/Excel/verbal).
-- Aplicar sobre una base de datos VIVA. Ejecutar dentro de una transacción.
-- Requiere haber aplicado antes add_vacaciones_saldo.sql.
-- ============================================================

BEGIN;

CREATE TABLE IF NOT EXISTS t_vacaciones_ajustes (
    id_ajuste           SERIAL,
    id_empleado         VARCHAR(20) NOT NULL,
    dias_ajuste         NUMERIC(5,2) NOT NULL CHECK (dias_ajuste <> 0),
    motivo              VARCHAR(255) NOT NULL,
    fecha_ajuste        DATE NOT NULL DEFAULT CURRENT_DATE,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_ajuste),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_vacaciones_ajustes_empleado ON t_vacaciones_ajustes (id_empleado);

COMMIT;
