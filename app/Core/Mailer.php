<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Envoi d'emails simple compatible PHPMailer (interface minimale).
 *
 * En l'absence de configuration SMTP en v1, les messages sont consignés.
 */
final class Mailer
{
    private string $fromName;
    private string $fromEmail;

    public function __construct()
    {
        $this->fromName  = $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'ATLÉX-SPORT';
        $this->fromEmail = $_ENV['MAIL_USER'] ?? getenv('MAIL_USER') ?: 'contact@atlex-sport.bj';
    }

    /**
     * Envoie un email. Retourne true en cas de succès.
     */
    public function send(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . sprintf('%s <%s>', $this->fromName, $this->fromEmail),
            'Reply-To: ' . $this->fromEmail,
        ];

        // En développement, on consigne au lieu d'envoyer réellement.
        if (defined('APP_ENV') && APP_ENV !== 'production') {
            $log = sprintf(
                "[%s] To: %s | Subject: %s\n%s\n---\n",
                date('c'),
                $to,
                $subject,
                strip_tags($body)
            );
            @file_put_contents(ROOT . '/storage/logs/mail.log', $log, FILE_APPEND);
            return true;
        }

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
