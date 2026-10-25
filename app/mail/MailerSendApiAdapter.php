<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Adaptador para MailerSend vía API REST.
 * Documentación: https://developers.mailersend.com/api/v1/email.html
 */
final class MailerSendApiAdapter extends AbstractApiMailer
{
    public function getName(): string
    {
        return 'MailerSend';
    }

    protected function endpoint(): string
    {
        return 'https://api.mailersend.com/v1/email';
    }

    protected function buildHeaders(): array
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'X-Requested-With: XMLHttpRequest',
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
            'from' => [
                'email' => $this->fromAddress,
                'name' => $this->fromName,
            ],
            'to' => [$recipient],
            'subject' => $subject,
            'html' => $body,
        ];

        if ($attachments !== []) {
            $payload['attachments'] = array_map(
                static fn (array $attachment): array => [
                    'filename' => $attachment['filename'],
                    'content' => $attachment['content'],
                ],
                $attachments,
            );
        }

        return $payload;
    }
}
