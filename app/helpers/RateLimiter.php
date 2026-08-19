<?php
/**
 * RateLimiter.php
 * Limita el envío repetido de correos por usuario y tipo.
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
        $limite = new DateTime("-{$this->ventanaMinutos} minutes");

        $sentencia = $this->conexion->prepare(
            "SELECT contador, ultimo_intento
             FROM t_intentos_correo
             WHERE id_usuario = :id_usuario
               AND tipo = :tipo
             LIMIT 1"
        );
        $sentencia->execute([
            ':id_usuario' => $idUsuario,
            ':tipo'       => $tipo,
        ]);
        $intento = $sentencia->fetch();

        if (!$intento) {
            return ['permitido' => true, 'minutos' => null];
        }

        $ultimo = new DateTime($intento['ultimo_intento']);

        if ($ultimo < $limite) {
            // Ventana expirada: reiniciar contador implícitamente.
            return ['permitido' => true, 'minutos' => null];
        }

        if ((int) $intento['contador'] >= $this->maxIntentos) {
            $minutosRestantes = $this->ventanaMinutos - (int) (time() - $ultimo->getTimestamp()) / 60;
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
             VALUES (:id_usuario, :tipo, 1, CURRENT_TIMESTAMP, :usr_insert, CURRENT_TIMESTAMP)
             ON CONFLICT (id_usuario, tipo)
             DO UPDATE SET
                 contador = CASE
                     WHEN EXCLUDED.ultimo_intento < (CURRENT_TIMESTAMP - INTERVAL '{$this->ventanaMinutos} minutes')
                     THEN 1
                     ELSE t_intentos_correo.contador + 1
                 END,
                 ultimo_intento = CURRENT_TIMESTAMP,
                 usr_update = EXCLUDED.usr_insert,
                 fec_update = CURRENT_TIMESTAMP"
        );
        $sentencia->execute([
            ':id_usuario' => $idUsuario,
            ':tipo'       => $tipo,
            ':usr_insert' => 'sistema_rate_limit',
        ]);
    }
}
