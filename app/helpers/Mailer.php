<?php
/**
 * Mailer.php
 * Helper reutilizable para enviar correos mediante PHPMailer usando SMTP.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private array $config;
    private PHPMailer $mail;

    public function __construct()
    {
        $configPath = __DIR__ . '/../../config/mail_config.php';
        if (!file_exists($configPath)) {
            throw new Exception('Archivo de configuración de correo no encontrado.');
        }

        $this->config = require $configPath;
        $this->mail = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host       = $this->config['host'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $this->config['username'];
        $this->mail->Password   = $this->config['password'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $this->config['port'];
        $this->mail->CharSet    = PHPMailer::CHARSET_UTF8;

        $this->mail->setFrom(
            $this->config['from_email'],
            $this->config['from_name']
        );
    }

    /**
     * Envía un correo.
     *
     * @param string $toEmail Correo del destinatario.
     * @param string $toName  Nombre del destinatario.
     * @param string $subject Asunto.
     * @param string $body    Cuerpo HTML.
     * @param string $altBody Cuerpo plano alternativo.
     *
     * @return bool
     */
    public function send(string $toEmail, string $toName, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody ?: strip_tags($body);

            return $this->mail->send();
        } catch (Exception $e) {
            error_log('Error enviando correo: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envía un correo masivo usando BCC (un único envío SMTP, sin revelar
     * las direcciones entre destinatarios).
     *
     * @param array<int, array{email:string, name:string}> $recipients Destinatarios.
     * @param string $subject Asunto.
     * @param string $body    Cuerpo HTML.
     * @param string $altBody Cuerpo plano alternativo.
     *
     * @return int Número de destinatarios notificados (0 si falló o no había destinatarios).
     */
    public function sendBulk(array $recipients, string $subject, string $body, string $altBody = ''): int
    {
        $recipients = array_values(array_filter($recipients, static function (array $recipient): bool {
            return !empty(trim((string) ($recipient['email'] ?? '')));
        }));

        if ($recipients === []) {
            return 0;
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->clearBCCs();

            foreach ($recipients as $recipient) {
                $this->mail->addBCC(
                    trim((string) $recipient['email']),
                    trim((string) ($recipient['name'] ?? ''))
                );
            }

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody ?: strip_tags($body);

            if (!$this->mail->send()) {
                return 0;
            }

            return count($recipients);
        } catch (Exception $e) {
            error_log('Error enviando correo masivo: ' . $this->mail->ErrorInfo);
            return 0;
        }
    }
}
