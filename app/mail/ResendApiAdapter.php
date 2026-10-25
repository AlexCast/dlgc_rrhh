<?php

declare(strict_types=1);

namespace App\Mail;

/**
 * Adaptador para Resend vía API REST.
 * Documentación: https://resend.com/docs/api-reference/emails/send-email
 */
final class ResendApiAdapter extends AbstractApiMailer
{
    public function getName(): string
    {
        return 'Resend';
    }

    protected function endpoint(): string
    {
        return 'https://api.resend.com/emails';
    }

    protected function buildHeaders(): array
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
    }

    protected function buildPayload(
        string $to,
        string $toName,
        string $subject,
        string $body,
        array $attachments,
    ): array {
        $from = $this->fromName !== ''
            ? sprintf('%s <%s>', $this->fromName, $this->fromAddress)
            : $this->fromAddress;

        $payload = [
            'from' => $from,
            'to' => [$to],
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
