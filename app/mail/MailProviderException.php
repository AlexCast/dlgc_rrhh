<?php

declare(strict_types=1);

namespace App\Mail;

use RuntimeException;

/**
 * Excepción de dominio para fallos de un proveedor de correo concreto.
 *
 * La lanza cada adaptador cuando la API responde con un código HTTP de error
 * (429, 401, 5xx, ...) o cuando se produce un timeout/error de red. El
 * orquestador (MultiMailerService) la captura para conmutar al siguiente
 * proveedor de la cadena.
 */
final class MailProviderException extends RuntimeException
{
    public function __construct(
        private readonly string $provider,
        string $message,
        private readonly ?int $httpCode = null,
        private readonly ?int $curlCode = null,
    ) {
        parent::__construct($message, $httpCode ?? $curlCode ?? 0);
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    public function getCurlCode(): ?int
    {
        return $this->curlCode;
    }
}
