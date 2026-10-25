<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Adaptador para Brevo (antes Sendinblue) vía API REST.
 * Documentación: https://developers.brevo.com/reference/sendtransacemail
 */
final class BrevoApiAdapter extends AbstractApiMailer
{
    public function getName(): string
    {
        return 'Brevo';
    }

    protected function endpoint(): string
    {
        return 'https://api.brevo.com/v3/smtp/email';
    }

    protected function buildHeaders(): array
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'api-key: ' . $this->apiKey,
        ];
    }

    protected function buildPayload(
        string $to,
        string $toName,
        string $subject,
        string $body,
        array $attachments,
    ): array {
        $recipient = ['email' => $to];
        if ($toName !== '') {
            $recipient['name'] = $toName;
        }

        $payload = [
            'sender' => [
                'name' => $this->fromName,
                'email' => $this->fromAddress,
            ],
            'to' => [$recipient],
            'subject' => $subject,
            'htmlContent' => $body,
        ];

        if ($attachments !== []) {
            $payload['attachment'] = array_map(
                static fn (array $attachment): array => [
                    'name' => $attachment['filename'],
                    'content' => $attachment['content'],
                ],
                $attachments,
            );
        }

        return $payload;
    }
}
