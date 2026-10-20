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

    /**
     * Lee una variable de entorno (getenv o SetEnv de Apache) y la normaliza.
     */
    private static function env(string $nombre): string
    {
        $valor = getenv($nombre);
        if (!is_string($valor) || trim($valor) === '') {
            $valor = $_SERVER[$nombre] ?? '';
        }

        return is_string($valor) ? trim($valor) : '';
    }

    private static function secretKey(): string
    {
        $desdeEntorno = self::env('TURNSTILE_SECRET');
        if ($desdeEntorno !== '') {
            return $desdeEntorno;
        }

        return trim((string) (self::config()['secret_key'] ?? ''));
    }

    /**
     * Hostnames que siteverify debe devolver para aceptar el token.
     * En producción solo debe contener el dominio real (sin localhost).
     *
     * @return string[]
     */
    private static function expectedHostnames(): array
    {
        $desdeEntorno = self::env('TURNSTILE_HOSTNAMES');
        $configurados = $desdeEntorno !== ''
            ? explode(',', $desdeEntorno)
            : (array) (self::config()['expected_hostnames'] ?? []);

        $normalizados = [];
        foreach ($configurados as $hostname) {
            $hostname = strtolower(trim((string) $hostname));
            if ($hostname !== '') {
                $normalizados[] = $hostname;
            }
        }

        return array_values(array_unique($normalizados));
    }

    public static function siteKey(): string
    {
        return trim((string) (self::config()['site_key'] ?? ''));
    }

    /**
     * Verifica el token contra la API de Cloudflare exigiendo success === true,
     * la acción esperada y un hostname aprobado. Falla cerrado ante cualquier
     * anomalía (sin token, sin secreto, timeout, HTTP no-200, JSON inválido).
     *
     * Usa el wrapper de streams de PHP en lugar de cURL para no depender de la
     * extensión php_curl (no siempre cargada en Apache/Windows).
     */
    public static function verify(?string $token, string $expectedAction): bool
    {
        if (!is_string($token) || trim($token) === '' || strlen($token) > 4096) {
            return false;
        }

        $secretKey = self::secretKey();

        if ($secretKey === '') {
            error_log('Turnstile: falta secret_key en la configuración o en TURNSTILE_SECRET.');
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

        $estadoHttp = 0;
        foreach ($http_response_header ?? [] as $cabecera) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $cabecera, $coincidencia) === 1) {
                $estadoHttp = (int) $coincidencia[1];
            }
        }

        if ($estadoHttp !== 200) {
            error_log('Turnstile: siteverify respondió HTTP ' . $estadoHttp . '.');
            return false;
        }

        $resultado = json_decode($respuesta, true);

        if (!is_array($resultado)) {
            error_log('Turnstile: la respuesta de siteverify no es JSON válido.');
            return false;
        }

        if (($resultado['success'] ?? false) !== true) {
            $errores = array_map('strval', (array) ($resultado['error-codes'] ?? []));
            error_log('Turnstile: token rechazado (' . implode(', ', $errores) . ').');
            return false;
        }

        $accionRecibida = (string) ($resultado['action'] ?? '');
        if ($expectedAction !== '' && !hash_equals($expectedAction, $accionRecibida)) {
            error_log('Turnstile: acción no coincide (esperada=' . $expectedAction . ', recibida=' . $accionRecibida . ').');
            return false;
        }

        $hostnamesPermitidos = self::expectedHostnames();
        if ($hostnamesPermitidos === []) {
            error_log('Turnstile: expected_hostnames no está configurado.');
            return false;
        }

        $hostnameRecibido = strtolower((string) ($resultado['hostname'] ?? ''));
        if ($hostnameRecibido === '' || !in_array($hostnameRecibido, $hostnamesPermitidos, true)) {
            error_log('Turnstile: hostname no aprobado (' . $hostnameRecibido . ').');
            return false;
        }

        return true;
    }
}
