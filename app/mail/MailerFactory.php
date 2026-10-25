<?php

declare(strict_types=1);

namespace App\Mail;

use RuntimeException;

/**
 * Factoría que construye el servicio multi-proveedor a partir de la
 * configuración de .env, respetando el orden de prioridad configurado.
 */
final class MailerFactory
{
    public static function create(): MultiMailerService
    {
        $config = MailConfig::fromEnvironment();

        $providers = [];
        foreach ($config->providers() as $name => $apiKey) {
            $providers[] = match ($name) {
                'Brevo' => new BrevoApiAdapter(
                    $apiKey,
                    $config->fromAddress(),
                    $config->fromName(),
                    $config->timeout(),
                ),
                'Resend' => new ResendApiAdapter(
                    $apiKey,
                    $config->fromAddress(),
                    $config->fromName(),
                    $config->timeout(),
                ),
                'MailerSend' => new MailerSendApiAdapter(
                    $apiKey,
                    $config->fromAddress(),
                    $config->fromName(),
                    $config->timeout(),
                ),
                default => throw new RuntimeException(sprintf('Proveedor de correo desconocido: %s', $name)),
            };
        }

        return new MultiMailerService($providers, $config->logFile());
    }
}
