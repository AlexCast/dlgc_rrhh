<?php

declare(strict_types=1);

namespace App\Mail;

interface MailerInterface
{
    /**
     * Envía un correo a través del proveedor implementado.
     *
     * @param string                       $to          Destinatario: "correo@dominio.com" o "Nombre <correo@dominio.com>".
     * @param string                       $subject     Asunto del correo.
     * @param string                       $body        Cuerpo HTML del correo.
     * @param list<array{filename:string, content:string, mime?:string}|array{path:string, filename?:string, mime?:string}> $attachments
     *                                                  Adjuntos opcionales (base64 en "content" o ruta en "path").
     *
     * @return bool true si el proveedor aceptó el envío.
     *
     * @throws MailProviderException Cuando el proveedor responde con un error
     *                               HTTP (4xx/5xx), excede la cuota o se produce
     *                               un timeout/error de red.
     */
    public function send(string $to, string $subject, string $body, array $attachments = []): bool;
}
