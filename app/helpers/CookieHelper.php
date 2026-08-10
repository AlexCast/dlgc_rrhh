<?php
declare(strict_types=1);

/**
 * Helper para operaciones seguras con cookies HTTP.
 *
 * Proporciona una API unificada para escribir/borrar cookies respetando
 * SameSite, HttpOnly y Secure, y una función específica para destruir la
 * cookie de sesión de forma consistente con session_bootstrap.php.
 */

class CookieHelper
{
    /**
     * Escribe una cookie con opciones seguras por defecto.
     *
     * @param string $name Nombre de la cookie.
     * @param string $value Valor de la cookie.
     * @param array<string, mixed> $options Opciones que sobreescriben los valores por defecto.
     *        Posibles claves: expires, path, domain, secure, httponly, samesite.
     */
    public static function set(string $name, string $value, array $options = []): void
    {
        $is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        $defaults = [
            'expires'  => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        $config = array_merge($defaults, $options);

        // Asegurar tipos correctos para setcookie.
        $config['expires']  = (int) $config['expires'];
        $config['path']     = (string) $config['path'];
        $config['domain']   = (string) $config['domain'];
        $config['secure']   = (bool) $config['secure'];
        $config['httponly'] = (bool) $config['httponly'];
        $config['samesite'] = (string) $config['samesite'];

        setcookie($name, $value, $config);
    }

    /**
     * Borra una cookie del navegador enviando una fecha de expiración en el pasado
     * y conservando path/domain/samesite consistentes.
     */
    public static function delete(string $name, array $options = []): void
    {
        $defaults = [
            'path'     => '/',
            'domain'   => '',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        $config = array_merge($defaults, $options);
        $config['expires'] = time() - 42000;

        self::set($name, '', $config);
    }

    /**
     * Borra la cookie de sesión activa respetando los parámetros con los que fue creada.
     * Útil durante logout o invalidación de sesión.
     */
    public static function deleteSessionCookie(): void
    {
        if (!ini_get('session.use_cookies') || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $params = session_get_cookie_params();
        self::delete(session_name(), [
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
}
