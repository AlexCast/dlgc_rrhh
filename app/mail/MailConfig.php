<?php

declare(strict_types=1);

namespace App\Mail;

use RuntimeException;

/**
 * Configuración centralizada del módulo de correo.
 *
 * Lee todas las credenciales desde el archivo .env (la misma fuente que usa
 * app/conexion.php para la base de datos), evitando secretos en código
 * versionado. Fallback de valores seguros para los campos opcionales.
 */
final class MailConfig
{
    /**
     * @param array<string, string> $providers Proveedor => API key, en orden de prioridad.
     */
    private function __construct(
        private readonly string $fromAddress,
        private readonly string $fromName,
        private readonly int $timeout,
        private readonly string $logFile,
        private readonly array $providers,
    ) {
    }

    public static function fromEnvironment(): self
    {
        require_once dirname(__DIR__) . '/helpers/EnvLoader.php';
        load_environment_file(dirname(__DIR__, 2) . '/.env');

        $env = static function (string $key): ?string {
            $value = getenv($key);
            return ($value === false || trim($value) === '') ? null : trim($value);
        };

        $fromAddress = $env('MAIL_FROM_ADDRESS');
        if ($fromAddress === null) {
            throw new RuntimeException('Falta MAIL_FROM_ADDRESS en .env');
        }

        $fromName = $env('MAIL_FROM_NAME') ?? 'Portal DLGC RRHH';

        $timeout = (int) ($env('MAILER_TIMEOUT') ?? '15');
        if ($timeout < 1) {
            $timeout = 15;
        }

        // Orden de prioridad: Brevo (1) -> Resend (2) -> MailerSend (3).
        $providers = [];
        foreach (['BREVO_API_KEY' => 'Brevo', 'RESEND_API_KEY' => 'Resend', 'MAILERSEND_API_KEY' => 'MailerSend'] as $envKey => $name) {
            $apiKey = $env($envKey);
            if ($apiKey !== null) {
                $providers[$name] = $apiKey;
            }
        }

        if ($providers === []) {
            throw new RuntimeException(
                'Ninguna API key de proveedor de correo configurada en .env '
                . '(BREVO_API_KEY, RESEND_API_KEY o MAILERSEND_API_KEY).'
            );
        }

        $logFile = $env('MAIL_LOG_PATH') ?? dirname(__DIR__, 2) . '/logs/mailer.log';

        return new self($fromAddress, $fromName, $timeout, $logFile, $providers);
    }

    public function fromAddress(): string
    {
        return $this->fromAddress;
    }

    public function fromName(): string
    {
        return $this->fromName;
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function logFile(): string
    {
        return $this->logFile;
    }

    /**
     * @return array<string, string>
     */
    public function providers(): array
    {
        return $this->providers;
    }
}
