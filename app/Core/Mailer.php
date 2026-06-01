<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envoi d'emails via SMTP authentifié (PHPMailer).
 *
 * En dehors de la production, ou si le SMTP n'est pas configuré, les messages
 * sont consignés dans storage/logs/mail.log au lieu d'être réellement envoyés.
 *
 * Configuration (.env) :
 *   MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS, MAIL_ENCRYPTION (tls|ssl),
 *   MAIL_FROM_NAME
 */
final class Mailer
{
    private string $fromName;
    private string $fromEmail;
    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private string $encryption;

    public function __construct()
    {
        $env = static fn (string $key, string $default = ''): string
            => (string) ($_ENV[$key] ?? getenv($key) ?: $default);

        $this->fromEmail  = $env('MAIL_USER', 'contact@atlexsport.com');
        $this->fromName   = $env('MAIL_FROM_NAME', 'ATLEX - Sport');
        $this->host       = $env('MAIL_HOST');
        $this->port       = (int) $env('MAIL_PORT', '587');
        $this->user       = $env('MAIL_USER');
        $this->pass       = $env('MAIL_PASS');
        $this->encryption = strtolower($env('MAIL_ENCRYPTION', 'tls'));
    }

    /**
     * Envoie un email HTML. Retourne true en cas de succès.
     */
    public function send(string $to, string $subject, string $body, ?string $toName = null): bool
    {
        $smtpReady = $this->host !== '' && $this->user !== '' && $this->pass !== '';
        $isProd    = defined('APP_ENV') && APP_ENV === 'production';

        // Dev, ou SMTP non configuré : on consigne plutôt que d'envoyer.
        if (!$isProd || !$smtpReady) {
            return $this->logToFile($to, $subject, $body, $smtpReady);
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->user;
            $mail->Password   = $this->pass;
            $mail->Port       = $this->port;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            if ($this->encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            $mail->addAddress($to, $toName ?? '');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body)));

            $mail->send();

            return true;
        } catch (MailException) {
            error_log('[Mailer] Échec envoi à ' . $to . ' : ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Consigne le message dans un fichier (mode dev / SMTP absent).
     */
    private function logToFile(string $to, string $subject, string $body, bool $smtpReady): bool
    {
        $reason = $smtpReady ? 'non-prod' : 'SMTP non configuré';
        $log = sprintf(
            "[%s] (%s) To: %s | Subject: %s\n%s\n---\n",
            date('c'),
            $reason,
            $to,
            $subject,
            trim(strip_tags($body))
        );

        $dir = ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        return (bool) @file_put_contents($dir . '/mail.log', $log, FILE_APPEND);
    }
}
