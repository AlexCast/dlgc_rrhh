<?php
/**
 * TurnstileHelper.php
 * Verificación server-side de retos Cloudflare Turnstile (anti-bot)
 * para los formularios de login y registro.
 */

declare(strict_types=1);

class TurnstileHelper
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            $configPath = __DIR__ . '/../../config/turnstile_config.php';
            self::$config = file_exists($configPath) ? require $configPath : [];
        }

        return self::$config;
    }

    public static function siteKey(): string
    {
        return (string) (self::config()['site_key'] ?? '');
    }

    /**
     * Verifica el token enviado por el widget de Turnstile contra la API de Cloudflare.
     *
     * Usa el wrapper de streams de PHP en lugar de cURL para no depender de la
     * extensión php_curl (no siempre cargada en Apache/Windows).
     */
    public static function verify(?string $token): bool
    {
        $secretKey = (string) (self::config()['secret_key'] ?? '');

        if ($secretKey === '' || !is_string($token) || $token === '') {
            return false;
        }

        $contexto = stream_context_create([
            'http' => [
                'method'         => 'POST',
                'header'         => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content'        => http_build_query([
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]),
                'timeout'        => 10,
                'ignore_errors'  => true,
            ],
        ]);

        $respuesta = @file_get_contents(self::VERIFY_URL, false, $contexto);

        if ($respuesta === false) {
            $detalle = error_get_last();
            $mensaje = is_array($detalle) ? ($detalle['message'] ?? 'sin respuesta') : 'sin respuesta';
            error_log('Error verificando Turnstile: ' . $mensaje);
            return false;
        }

        $resultado = json_decode($respuesta, true);

        return is_array($resultado) && ($resultado['success'] ?? false) === true;
    }
}
