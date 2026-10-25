<?php

declare(strict_types=1);

namespace App\Mail;

use InvalidArgumentException;
use Throwable;

/**
 * Orquestador de envío con conmutación por fallo (failover).
 *
 * Recibe una colección de proveedores ordenados por prioridad e intenta el
 * envío con cada uno hasta conseguir una respuesta exitosa o agotar la lista.
 * Cada fallo y conmutación se registra en un archivo de log local.
 */
final class MultiMailerService implements MailerInterface
{
    /** @var list<MailerInterface> */
    private array $providers;

    /**
     * @param iterable<MailerInterface> $providers Proveedores en orden de prioridad.
     */
    public function __construct(
        iterable $providers,
        private readonly string $logFile,
    ) {
        $filtered = [];
        foreach ($providers as $provider) {
            if ($provider instanceof MailerInterface) {
                $filtered[] = $provider;
            }
        }

        if ($filtered === []) {
            throw new InvalidArgumentException('MultiMailerService requiere al menos un proveedor MailerInterface.');
        }

        $this->providers = $filtered;
    }

    public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $total = count($this->providers);

        foreach ($this->providers as $index => $provider) {
            $name = $this->providerName($provider);
            $next = $index + 1 < $total
                ? $this->providerName($this->providers[$index + 1])
                : null;

            try {
                if ($provider->send($to, $subject, $body, $attachments)) {
                    $this->log(sprintf(
                        'EXITO proveedor="%s" destinatario="%s" asunto="%s"',
                        $name,
                        $to,
                        $this->truncate($subject),
                    ));

                    return true;
                }

                $this->log(sprintf(
                    'FALLO proveedor="%s" error="send() devolvió false"%s',
                    $name,
                    $next !== null ? sprintf(' reintento="%s"', $next) : ' sin-mas-proveedores',
                ));
            } catch (MailProviderException $exception) {
                $this->log(sprintf(
                    'FALLO proveedor="%s" http=%s curl=%s error="%s"%s',
                    $exception->getProvider(),
                    $exception->getHttpCode() ?? 'n/a',
                    $exception->getCurlCode() ?? 'n/a',
                    $this->truncate($exception->getMessage()),
                    $next !== null ? sprintf(' reintento="%s"', $next) : ' sin-mas-proveedores',
                ));
            } catch (Throwable $exception) {
                $this->log(sprintf(
                    'FALLO proveedor="%s" excepcion="%s" error="%s"%s',
                    $name,
                    get_class($exception),
                    $this->truncate($exception->getMessage()),
                    $next !== null ? sprintf(' reintento="%s"', $next) : ' sin-mas-proveedores',
                ));
            }
        }

        return false;
    }

    private function providerName(MailerInterface $provider): string
    {
        if (method_exists($provider, 'getName')) {
            $name = $provider->getName();
            if (is_string($name) && $name !== '') {
                return $name;
            }
        }

        return get_debug_type($provider);
    }

    private function truncate(string $value, int $length = 200): string
    {
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

        return mb_strlen($value) > $length
            ? mb_substr($value, 0, $length) . '…'
            : $value;
    }

    private function log(string $message): void
    {
        $directory = dirname($this->logFile);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        @file_put_contents(
            $this->logFile,
            sprintf('[%s] %s%s', date('Y-m-d H:i:s'), $message, PHP_EOL),
            FILE_APPEND | LOCK_EX,
        );
    }
}
