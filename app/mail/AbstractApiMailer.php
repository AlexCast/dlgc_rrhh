<?php

declare(strict_types=1);

namespace App\Mail;

use InvalidArgumentException;

/**
 * Base común para los adaptadores de API REST.
 *
 * Concentra la lógica de transporte HTTP/JSON con cURL nativo (timeouts,
 * detección de errores de red y serialización) para que cada adaptador solo
 * defina su endpoint, sus cabeceras y el formato de su payload.
 */
abstract class AbstractApiMailer implements MailerInterface
{
    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $fromAddress,
        protected readonly string $fromName,
        protected readonly int $timeout = 15,
    ) {
    }

    /** Nombre legible del proveedor (usado en logs y excepciones). */
    abstract public function getName(): string;

    /** URL del endpoint HTTP/JSON del proveedor. */
    abstract protected function endpoint(): string;

    /**
     * Cabeceras HTTP de la petición (formato "Clave: valor").
     *
     * @return list<string>
     */
    abstract protected function buildHeaders(): array;

    /**
     * Construye el cuerpo JSON específico del proveedor.
     *
     * @param list<array{filename:string, content:string, mime:string}> $attachments
     * @return array<string, mixed>
     */
    abstract protected function buildPayload(
        string $to,
        string $toName,
        string $subject,
        string $body,
        array $attachments,
    ): array;

    final public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        [$email, $name] = $this->parseRecipient($to);
        if ($email === '') {
            throw new InvalidArgumentException('El destinatario del correo no puede estar vacío.');
        }

        if (!function_exists('curl_init')) {
            throw new MailProviderException($this->getName(), 'La extensión cURL no está disponible en este servidor.', null, 0);
        }

        $payload = $this->buildPayload(
            $email,
            $name,
            $subject,
            $body,
            $this->normalizeAttachments($attachments),
        );

        [$httpCode, $responseBody] = $this->postJson(
            $this->endpoint(),
            $this->buildHeaders(),
            $payload,
        );

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        throw new MailProviderException(
            $this->getName(),
            $this->describeError($httpCode, $responseBody),
            $httpCode,
        );
    }

    /**
     * Ejecuta la petición POST JSON con cURL nativo.
     *
     * @param list<string>          $headers
     * @param array<string, mixed>  $payload
     * @return array{0: int, 1: string} Código HTTP y cuerpo de la respuesta.
     */
    protected function postJson(string $url, array $headers, array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new MailProviderException($this->getName(), 'No se pudo serializar la petición: ' . json_last_error_msg());
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new MailProviderException($this->getName(), 'No se pudo inicializar cURL.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        $responseBody = curl_exec($handle);
        $curlErrno = curl_errno($handle);
        $curlError = curl_error($handle);
        $httpCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($curlErrno !== 0) {
            throw new MailProviderException(
                $this->getName(),
                'Error de red/timeout: ' . $curlError,
                $httpCode > 0 ? $httpCode : null,
                $curlErrno,
            );
        }

        return [$httpCode, is_string($responseBody) ? $responseBody : ''];
    }

    /**
     * Separa "Nombre <correo@dominio.com>" en [correo, nombre].
     *
     * @return array{0: string, 1: string}
     */
    protected function parseRecipient(string $to): array
    {
        $to = trim($to);

        if (preg_match('/^(.+?)\s*<([^<>]+)>$/', $to, $matches) === 1) {
            $name = trim($matches[1], " \t\n\r\0\x0B\"'");
            return [trim($matches[2]), $name];
        }

        return [$to, ''];
    }

    /**
     * Normaliza la lista de adjuntos a un formato común
     * (filename, content en base64 y mime).
     *
     * @return list<array{filename:string, content:string, mime:string}>
     */
    protected function normalizeAttachments(array $attachments): array
    {
        $normalized = [];

        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) {
                continue;
            }

            $filename = trim((string) ($attachment['filename'] ?? $attachment['name'] ?? ''));
            $content = $attachment['content'] ?? null;
            $path = $attachment['path'] ?? null;
            $mime = trim((string) ($attachment['mime'] ?? $attachment['mime_type'] ?? ''));

            // Soporta adjuntos por ruta de archivo (se lee y codifica en base64).
            if ($content === null && is_string($path) && $path !== '') {
                if (!is_readable($path)) {
                    throw new MailProviderException($this->getName(), 'Adjunto no legible: ' . $path);
                }

                $raw = file_get_contents($path);
                if ($raw === false) {
                    throw new MailProviderException($this->getName(), 'No se pudo leer el adjunto: ' . $path);
                }

                $content = base64_encode($raw);
                if ($filename === '') {
                    $filename = basename($path);
                }
                if ($mime === '' && function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if ($finfo !== false) {
                        $detected = finfo_file($finfo, $path);
                        $mime = is_string($detected) ? $detected : '';
                    }
                }
            }

            if (!is_string($content) || $content === '' || $filename === '') {
                continue;
            }

            if ($mime === '') {
                $mime = 'application/octet-stream';
            }

            $normalized[] = [
                'filename' => $filename,
                'content' => $content,
                'mime' => $mime,
            ];
        }

        return $normalized;
    }

    /**
     * Extrae un mensaje legible de la respuesta de error del proveedor.
     */
    protected function describeError(int $httpCode, string $responseBody): string
    {
        $detail = '';
        $decoded = json_decode($responseBody, true);

        if (is_array($decoded)) {
            foreach (['message', 'error', 'errors', 'detail'] as $field) {
                if (!array_key_exists($field, $decoded)) {
                    continue;
                }

                $value = $decoded[$field];
                if (is_string($value)) {
                    $detail = $value;
                    break;
                }
                if (is_array($value) || is_object($value)) {
                    $detail = (string) json_encode($value);
                    break;
                }
                $detail = (string) $value;
                break;
            }
        }

        $detail = trim($detail);
        if ($detail === '') {
            $detail = trim((string) preg_replace('/\s+/', ' ', strip_tags($responseBody)));
            $detail = substr($detail, 0, 200);
        } else {
            $detail = substr($detail, 0, 300);
        }

        return sprintf('HTTP %d%s', $httpCode, $detail !== '' ? ': ' . $detail : '');
    }
}
