<?php
/**
 * RateLimiter.php
 * Limita intentos repetidos por usuario y tipo ('login', 'verificacion', 'recuperacion').
 *
 * Reglas:
 * - Máximo 3 intentos por ventana de 15 minutos.
 * - Si la ventana expiró, se reinicia el contador.
 * - Si se excede, devuelve minutos restantes para reintentar.
 */

declare(strict_types=1);

class RateLimiter
{
    private PDO $conexion;
    private int $maxIntentos;
    private int $ventanaMinutos;

    public function __construct(PDO $conexion, int $maxIntentos = 3, int $ventanaMinutos = 15)
    {
        $this->conexion = $conexion;
        $this->maxIntentos = $maxIntentos;
        $this->ventanaMinutos = $ventanaMinutos;
    }

    /**
     * Verifica si se permite enviar otro correo.
     *
     * @param string $idUsuario
     * @param string $tipo 'verificacion' o 'recuperacion'
     *
     * @return array ['permitido' => bool, 'minutos' => int|null]
     */
    public function verificar(string $idUsuario, string $tipo): array
    {
        // La antigüedad del intento se calcula en SQL para usar la misma zona
        // horaria que CURRENT_TIMESTAMP y no depender de date.timezone de PHP.
        $sentencia = $this->conexion->prepare(
            "SELECT contador,
                    (ultimo_intento < (CURRENT_TIMESTAMP - INTERVAL {$this->ventanaMinutos} MINUTE)) AS ventana_expirada,
                    TIMESTAMPDIFF(MINUTE, ultimo_intento, CURRENT_TIMESTAMP) AS minutos_transcurridos
             FROM t_intentos_correo
             WHERE id_usuario = ?
               AND tipo = ?
             LIMIT 1"
        );
        $sentencia->execute([$idUsuario, $tipo]);
        $intento = $sentencia->fetch();

        if (!$intento) {
            return ['permitido' => true, 'minutos' => null];
        }

        if ((int) $intento['ventana_expirada'] === 1) {
            // Ventana expirada: reiniciar contador implícitamente.
            return ['permitido' => true, 'minutos' => null];
        }

        if ((int) $intento['contador'] >= $this->maxIntentos) {
            $minutosRestantes = $this->ventanaMinutos - (int) $intento['minutos_transcurridos'];
            if ($minutosRestantes < 1) {
                $minutosRestantes = 1;
            }

            return ['permitido' => false, 'minutos' => $minutosRestantes];
        }

        return ['permitido' => true, 'minutos' => null];
    }

    /**
     * Registra un nuevo intento de envío de correo.
     */
    public function registrar(string $idUsuario, string $tipo): void
    {
        $sentencia = $this->conexion->prepare(
            "INSERT INTO t_intentos_correo (id_usuario, tipo, contador, ultimo_intento, usr_insert, fec_insert)
             VALUES (?, ?, 1, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE
                 contador = IF(
                     VALUES(ultimo_intento) < (CURRENT_TIMESTAMP - INTERVAL {$this->ventanaMinutos} MINUTE),
                     1,
                     t_intentos_correo.contador + 1
                 ),
                 ultimo_intento = CURRENT_TIMESTAMP,
                 usr_update = VALUES(usr_insert),
                 fec_update = CURRENT_TIMESTAMP"
        );
        $sentencia->execute([$idUsuario, $tipo, 'sistema_rate_limit']);
    }
}
