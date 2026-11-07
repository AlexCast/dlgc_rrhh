<?php
/**
 * UploadRateLimiter.php
 * Limita subidas de archivos por usuario y tipo
 * ('foto_perfil', 'evidencias', 'gestor_subida', 'documento_subida').
 *
 * Mismo patrón que RateLimiter (t_intentos_correo) pero sobre
 * t_intentos_subida y con límites configurables por endpoint.
 *
 * Reglas:
 * - Máximo $maxIntentos subidas por ventana de $ventanaMinutos minutos.
 * - Si la ventana expiró, el contador se reinicia implícitamente.
 * - Si se excede, devuelve los minutos restantes para reintentar.
 */

declare(strict_types=1);

class UploadRateLimiter
{
    private PDO $conexion;
    private int $maxIntentos;
    private int $ventanaMinutos;

    public function __construct(PDO $conexion, int $maxIntentos, int $ventanaMinutos)
    {
        $this->conexion = $conexion;
        $this->maxIntentos = $maxIntentos;
        $this->ventanaMinutos = $ventanaMinutos;
    }

    /**
     * @return array{permitido: bool, minutos: ?int}
     */
    public function verificar(string $idUsuario, string $tipo): array
    {
        $sentencia = $this->conexion->prepare(
            "SELECT contador,
                    (ultimo_intento < (CURRENT_TIMESTAMP - INTERVAL {$this->ventanaMinutos} MINUTE)) AS ventana_expirada,
                    TIMESTAMPDIFF(MINUTE, ultimo_intento, CURRENT_TIMESTAMP) AS minutos_transcurridos
             FROM t_intentos_subida
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
     * Registra una nueva subida (se llama al inicio del procesamiento).
     */
    public function registrar(string $idUsuario, string $tipo): void
    {
        $sentencia = $this->conexion->prepare(
            "INSERT INTO t_intentos_subida (id_usuario, tipo, contador, ultimo_intento, usr_insert, fec_insert)
             VALUES (?, ?, 1, CURRENT_TIMESTAMP, 'sistema_rate_limit', CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE
                 contador = IF(
                     VALUES(ultimo_intento) < (CURRENT_TIMESTAMP - INTERVAL {$this->ventanaMinutos} MINUTE),
                     1,
                     t_intentos_subida.contador + 1
                 ),
                 ultimo_intento = CURRENT_TIMESTAMP,
                 usr_update = VALUES(usr_insert),
                 fec_update = CURRENT_TIMESTAMP"
        );
        $sentencia->execute([$idUsuario, $tipo]);
    }
}
