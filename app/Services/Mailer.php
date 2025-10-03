<?php

namespace App\Services;

use App\Core\Config;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class Mailer
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $settings = Config::get('mail');

        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isSMTP();
        $this->mailer->Host = $settings['host'] ?? '';
        $this->mailer->Port = $settings['port'] ?? 587;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $settings['username'] ?? '';
        $this->mailer->Password = $settings['password'] ?? '';

        $encryption = $settings['encryption'] ?? '';
        if ($encryption !== '') {
            $this->mailer->SMTPSecure = $encryption;
        }

        $fromAddress = $settings['from_address'] ?? '';
        $fromName = $settings['from_name'] ?? 'Gestor de Titulación';

        if ($fromAddress === '' || $this->mailer->Host === '' || $this->mailer->Username === '' || $this->mailer->Password === '') {
            throw new RuntimeException('La configuración SMTP es inválida o está incompleta.');
        }

        try {
            $this->mailer->setFrom($fromAddress, $fromName);
        } catch (MailException $exception) {
            throw new RuntimeException('No fue posible configurar el remitente del correo: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public function send(string $recipient, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        try {
            $mailer = clone $this->mailer;
            $mailer->clearAllRecipients();
            $mailer->addAddress($recipient);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = $textBody ?? strip_tags($htmlBody);
            $mailer->send();
        } catch (MailException $exception) {
            throw new RuntimeException('Error al enviar el correo electrónico: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
