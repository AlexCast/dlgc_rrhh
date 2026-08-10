-- Migración: tabla de tokens persistentes para "Recordarme".
-- Diseño seguro: selector plano + hash SHA-256 del validator. NUNCA se guarda el validator en claro.

CREATE TABLE IF NOT EXISTS t_remember_tokens (
    id SERIAL PRIMARY KEY,
    selector VARCHAR(12) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    id_usuario VARCHAR(20) NOT NULL REFERENCES t_usuarios(id_usuario),
    fec_expiracion TIMESTAMP NOT NULL,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP
);

-- Índice para limpieza/lookup por usuario.
CREATE INDEX IF NOT EXISTS idx_remember_tokens_id_usuario
    ON t_remember_tokens(id_usuario)
    WHERE fec_delete IS NULL;

-- Trigger de auditoría estándar del proyecto (insert/update).
DROP TRIGGER IF EXISTS tri_audit_usuarios ON t_remember_tokens;
CREATE TRIGGER tri_audit_usuarios
BEFORE INSERT OR UPDATE ON t_remember_tokens
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();
