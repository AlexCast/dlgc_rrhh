<?php
declare(strict_types=1);

require_once __DIR__ . '/CookieHelper.php';

/**
 * Gestión segura de cookies persistentes "Recordarme".
 *
 * Patrón selector + validator:
 *   - El selector es un identificador público de 12 caracteres hexadecimales.
 *   - El validator es un secreto de 64 caracteres hexadecimales.
 *   - En BD se almacena SHA-256 del validator, nunca el validator en claro.
 *   - La cookie contiene "selector:validator".
 *
 * Al validar con éxito se rota el token (nuevo selector+validator) para limitar
 * el impacto de un token robado.
 */
class RememberMeHelper
{
    private const COOKIE_NAME = 'remember';
    private const SELECTOR_BYTES = 6;
    private const VALIDATOR_BYTES = 32;
    private const COOKIE_DAYS = 30;

    /**
     * Crea un token "Recordarme" para el usuario y lo envía como cookie.
     * Devuelve true si tuvo éxito.
     *
     * Antes de insertar:
     *   - Poda tokens expirados/globalmente.
     *   - Limpia tokens previos del mismo usuario para no acumular basura.
     */
    public static function create(PDO $conexion, string $idUsuario): bool
    {
        if ($idUsuario === '') {
            return false;
        }

        self::pruneExpired($conexion);
        self::deleteAllForUser($conexion, $idUsuario);

        [$selector, $validator, $hash] = self::generateTokenParts();

        $stmt = $conexion->prepare(
            "INSERT INTO t_remember_tokens (selector, token_hash, id_usuario, fec_expiracion)
             VALUES (:selector, :token_hash, :id_usuario, CURRENT_TIMESTAMP + INTERVAL '30 days')"
        );

        try {
            $stmt->execute([
                ':selector'   => $selector,
                ':token_hash' => $hash,
                ':id_usuario' => $idUsuario,
            ]);
        } catch (PDOException $e) {
            error_log('Error al crear remember token: ' . $e->getMessage());
            return false;
        }

        self::setCookie($selector, $validator);
        return true;
    }

    /**
     * Intenta validar la cookie "remember" y autenticar al usuario.
     * Si es válido, rota el token y devuelve el id_usuario.
     * Si no es válido, devuelve null.
     */
    public static function validate(PDO $conexion): ?string
    {
        $parts = self::parseCookie();
        if ($parts === null) {
            return null;
        }

        [$selector, $validator] = $parts;

        $stmt = $conexion->prepare(
            "SELECT id, id_usuario, token_hash, fec_expiracion
             FROM t_remember_tokens
             WHERE selector = :selector
               AND fec_delete IS NULL
               AND fec_expiracion > CURRENT_TIMESTAMP"
        );
        $stmt->execute([':selector' => $selector]);
        $row = $stmt->fetch();

        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
            self::deleteCookie();
            return null;
        }

        // Rotar token: crear uno nuevo y marcar el anterior como usado.
        [$newSelector, $newValidator, $newHash] = self::generateTokenParts();

        try {
            $update = $conexion->prepare(
                "UPDATE t_remember_tokens
                 SET selector = :new_selector,
                     token_hash = :new_hash,
                     fec_expiracion = CURRENT_TIMESTAMP + INTERVAL '30 days',
                     fec_update = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $update->execute([
                ':new_selector' => $newSelector,
                ':new_hash'     => $newHash,
                ':id'           => $row['id'],
            ]);
        } catch (PDOException $e) {
            error_log('Error al rotar remember token: ' . $e->getMessage());
            // Aunque falle la rotación, permitimos el login; el token actual sigue válido.
            return (string) $row['id_usuario'];
        }

        self::setCookie($newSelector, $newValidator);
        return (string) $row['id_usuario'];
    }

    /**
     * Invalida el token actual de la cookie y elimina la cookie del navegador.
     */
    public static function invalidateCurrent(PDO $conexion): void
    {
        $parts = self::parseCookie();
        if ($parts !== null) {
            [$selector] = $parts;
            try {
                $stmt = $conexion->prepare(
                    "UPDATE t_remember_tokens
                     SET fec_delete = CURRENT_TIMESTAMP,
                         usr_delete = NULLIF(current_setting('app.current_user', true), '')
                     WHERE selector = :selector AND fec_delete IS NULL"
                );
                $stmt->execute([':selector' => $selector]);
            } catch (PDOException $e) {
                error_log('Error al invalidar remember token: ' . $e->getMessage());
            }
        }

        self::deleteCookie();
    }

    /**
     * Invalida TODOS los tokens persistentes de un usuario (p.ej. al cambiar contraseña).
     *
     * Hace soft-delete; pruneExpired() los borrará físicamente después.
     */
    public static function invalidateAllForUser(PDO $conexion, string $idUsuario): void
    {
        try {
            $stmt = $conexion->prepare(
                "UPDATE t_remember_tokens
                 SET fec_delete = CURRENT_TIMESTAMP,
                     usr_delete = NULLIF(current_setting('app.current_user', true), '')
                 WHERE id_usuario = :id_usuario AND fec_delete IS NULL"
            );
            $stmt->execute([':id_usuario' => $idUsuario]);
        } catch (PDOException $e) {
            error_log('Error al invalidar tokens de usuario: ' . $e->getMessage());
        }
    }

    /**
     * Borra físicamente todos los tokens (activos o no) de un usuario.
     * Se usa al crear uno nuevo para mantener un único token por usuario.
     */
    private static function deleteAllForUser(PDO $conexion, string $idUsuario): void
    {
        try {
            $stmt = $conexion->prepare(
                "DELETE FROM t_remember_tokens
                 WHERE id_usuario = :id_usuario"
            );
            $stmt->execute([':id_usuario' => $idUsuario]);
        } catch (PDOException $e) {
            error_log('Error al borrar tokens previos de usuario: ' . $e->getMessage());
        }
    }

    /**
     * Elimina tokens expirados o con soft-delete antiguo de la base de datos.
     *
     * Conserva un margen de 7 días después de la expiración por si se necesita
     * forense; tokens marcados como borrados se limpian al mismo tiempo.
     */
    public static function pruneExpired(PDO $conexion): void
    {
        try {
            $stmt = $conexion->prepare(
                "DELETE FROM t_remember_tokens
                 WHERE fec_expiracion < CURRENT_TIMESTAMP - INTERVAL '7 days'
                    OR fec_delete IS NOT NULL"
            );
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error al podar remember tokens: ' . $e->getMessage());
        }
    }

    private static function generateTokenParts(): array
    {
        $selector  = bin2hex(random_bytes(self::SELECTOR_BYTES));
        $validator = bin2hex(random_bytes(self::VALIDATOR_BYTES));
        $hash      = hash('sha256', $validator);
        return [$selector, $validator, $hash];
    }

    private static function setCookie(string $selector, string $validator): void
    {
        $value = $selector . ':' . $validator;
        CookieHelper::set(self::COOKIE_NAME, $value, [
            'expires'  => time() + (self::COOKIE_DAYS * 24 * 60 * 60),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function deleteCookie(): void
    {
        CookieHelper::delete(self::COOKIE_NAME, ['path' => '/']);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private static function parseCookie(): ?array
    {
        $value = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (!is_string($value) || $value === '') {
            return null;
        }

        $parts = explode(':', $value, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$selector, $validator] = $parts;
        if (!ctype_xdigit($selector) || !ctype_xdigit($validator)) {
            return null;
        }

        return [$selector, $validator];
    }
}
