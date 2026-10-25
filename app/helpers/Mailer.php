<?php
/**
 * Mailer.php
 * Fachada de envío de correos del proyecto.
 *
 * Mantiene la API pública histórica (send/sendBulk) sobre la nueva
 * arquitectura multi-proveedor basada en APIs REST (Brevo, Resend y
 * MailerSend) con conmutación por fallo. La configuración y las API keys
 * se leen desde .env vía App\Mail\MailConfig.
 */

declare(strict_types=1);

require_once __DIR__ . '/../mail/autoload.php';

use App\Mail\MailerFactory;
use App\Mail\MultiMailerService;

class Mailer
{
    private ?MultiMailerService $service = null;

    /**
     * Envía un correo.
     *
     * @param string $toEmail Correo del destinatario.
     * @param string $toName  Nombre del destinatario.
     * @param string $subject Asunto.
     * @param string $body    Cuerpo HTML.
     * @param string $altBody Cuerpo plano alternativo (no usado por las APIs REST; se conserva por compatibilidad).
     *
     * @return bool true si algún proveedor aceptó el envío.
     */
    public function send(string $toEmail, string $toName, string $subject, string $body, string $altBody = ''): bool
    {
        $service = $this->service();
        if ($service === null) {
            return false;
        }

        try {
            return $service->send(
                $this->formatRecipient($toEmail, $toName),
                $subject,
                $body,
            );
        } catch (\Throwable $exception) {
            error_log('Mailer: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Envía correos individuales a una lista de destinatarios.
     *
     * Con las APIs REST no existe BCC a nivel de transporte, por lo que cada
     * destinatario recibe un envío independiente; el failover se aplica por
     * mensaje y se cuentan los envíos aceptados.
     *
     * @param array<int, array{email:string, name?:string}> $recipients Destinatarios.
     * @param string $subject Asunto.
     * @param string $body    Cuerpo HTML.
     * @param string $altBody Cuerpo plano alternativo (compatibilidad).
     *
     * @return int Número de destinatarios notificados.
     */
    public function sendBulk(array $recipients, string $subject, string $body, string $altBody = ''): int
    {
        $service = $this->service();
        if ($service === null) {
            return 0;
        }

        $sent = 0;

        foreach ($recipients as $recipient) {
            if (!is_array($recipient)) {
                continue;
            }

            $email = trim((string) ($recipient['email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $name = trim((string) ($recipient['name'] ?? ''));

            try {
                if ($service->send($this->formatRecipient($email, $name), $subject, $body)) {
                    $sent++;
                }
            } catch (\Throwable $exception) {
                error_log('Mailer: ' . $exception->getMessage());
            }
        }

        return $sent;
    }

    private function service(): ?MultiMailerService
    {
        if ($this->service === null) {
            try {
                $this->service = MailerFactory::create();
            } catch (\Throwable $exception) {
                error_log('Mailer: ' . $exception->getMessage());
                $this->service = null;
            }
        }

        return $this->service;
    }

    private function formatRecipient(string $email, string $name): string
    {
        $email = trim($email);
        $name = trim($name);

        return $name !== '' ? sprintf('%s <%s>', $name, $email) : $email;
    }
}