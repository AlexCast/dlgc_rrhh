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
     */
    public static function verify(?string $token): bool
    {
        $secretKey = (string) (self::config()['secret_key'] ?? '');

        if ($secretKey === '' || !is_string($token) || $token === '') {
            return false;
        }

        $ch = curl_init(self::VERIFY_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'secret'   => $secretKey,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $respuesta = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false || $error !== '') {
            error_log('Error verificando Turnstile: ' . $error);
            return false;
        }

        $resultado = json_decode($respuesta, true);

        return is_array($resultado) && ($resultado['success'] ?? false) === true;
    }
}
