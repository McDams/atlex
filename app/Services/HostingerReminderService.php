<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Mailer;
use App\Models\Setting;

/**
 * Service d'envoi de rappels email pour les expirations Hostinger.
 *
 * Vérifie les abonnements et domaines et envoie des emails
 * automatiques à l'administrateur lorsqu'une expiration approche
 * (à 30, 14 ou 7 jours).
 *
 * Anti-doublon : chaque rappel envoyé est tracé dans la table `settings`
 * avec la clé `hostinger_reminder_sent_{id}_{days}` pour éviter
 * d'envoyer plusieurs fois le même email.
 */
final class HostingerReminderService
{
    /** Seuils de rappel en jours (ordre décroissant). */
    private const REMINDER_THRESHOLDS = [30, 14, 7];

    /** Préfixe de clé dans la table settings pour l'anti-doublon. */
    private const REMINDER_KEY_PREFIX = 'hostinger_reminder_sent_';

    private HostingerService $hostingerService;
    private Mailer $mailer;
    private Setting $setting;

    /** Email de l'administrateur (chargé depuis settings). */
    private string $adminEmail;

    public function __construct()
    {
        $setting              = new Setting();
        $token                = $setting->get('hostinger_api_token') ?? '';
        $this->hostingerService = new HostingerService($token);
        $this->mailer         = new Mailer();
        $this->setting        = $setting;

        // Email admin : settings DB > variable d'environnement > valeur par défaut
        $this->adminEmail = $setting->get('admin_email')
            ?? $_ENV['ADMIN_EMAIL']
            ?? getenv('ADMIN_EMAIL')
            ?: 'admin@atlexsport.com';
    }

    // -------------------------------------------------------------------------
    // Méthode principale
    // -------------------------------------------------------------------------

    /**
     * Vérifie tous les abonnements et domaines Hostinger,
     * et envoie les rappels email appropriés.
     *
     * À exécuter quotidiennement via crontab.
     */
    public function sendExpirationReminders(): void
    {
        // Vérifier que le token est configuré
        $token = $this->setting->get('hostinger_api_token') ?? '';
        if ($token === '') {
            $this->log('Token API Hostinger non configuré. Rappels ignorés.');
            return;
        }

        $this->log('Démarrage de la vérification des expirations Hostinger...');

        try {
            $subscriptions = $this->hostingerService->getSubscriptions();
            $domains       = $this->hostingerService->getDomains();
        } catch (\RuntimeException $e) {
            $this->log('Erreur API Hostinger : ' . $e->getMessage());
            return;
        }

        $remindersSent = 0;

        // Rappels pour les abonnements
        foreach ($subscriptions as $sub) {
            $id       = (string) ($sub['id'] ?? md5($sub['name']));
            $daysLeft = $sub['days_left'] ?? null;
            $name     = $sub['name'] ?? 'Abonnement inconnu';

            if ($daysLeft === null) {
                continue;
            }

            foreach (self::REMINDER_THRESHOLDS as $threshold) {
                if ($daysLeft <= $threshold) {
                    $key = self::REMINDER_KEY_PREFIX . 'sub_' . $id . '_' . $threshold;

                    // Anti-doublon : vérifier si un rappel a déjà été envoyé aujourd'hui
                    if ($this->hasReminderBeenSentToday($key)) {
                        continue;
                    }

                    // Envoyer l'email
                    $subject = "⚠️ ATLÉX-SPORT — Abonnement expirant dans {$daysLeft} jour(s) : {$name}";
                    $body    = $this->buildSubscriptionEmailBody($sub, $daysLeft, $threshold);

                    if ($this->mailer->send($this->adminEmail, $subject, $body)) {
                        $this->markReminderSent($key);
                        $this->log("Rappel envoyé pour abonnement «{$name}» ({$daysLeft}j restants, seuil {$threshold}j).");
                        $remindersSent++;
                    }

                    // N'envoyer qu'un seul seuil par item et par exécution
                    break;
                }
            }
        }

        // Rappels pour les domaines
        foreach ($domains as $domain) {
            $name     = $domain['domain'] ?? 'domaine inconnu';
            $id       = preg_replace('/[^a-z0-9._-]/', '_', strtolower($name));
            $daysLeft = $domain['days_left'] ?? null;

            if ($daysLeft === null) {
                continue;
            }

            foreach (self::REMINDER_THRESHOLDS as $threshold) {
                if ($daysLeft <= $threshold) {
                    $key = self::REMINDER_KEY_PREFIX . 'domain_' . $id . '_' . $threshold;

                    if ($this->hasReminderBeenSentToday($key)) {
                        continue;
                    }

                    $subject = "⚠️ ATLÉX-SPORT — Domaine expirant dans {$daysLeft} jour(s) : {$name}";
                    $body    = $this->buildDomainEmailBody($domain, $daysLeft, $threshold);

                    if ($this->mailer->send($this->adminEmail, $subject, $body)) {
                        $this->markReminderSent($key);
                        $this->log("Rappel envoyé pour domaine «{$name}» ({$daysLeft}j restants, seuil {$threshold}j).");
                        $remindersSent++;
                    }

                    break;
                }
            }
        }

        $this->log("Vérification terminée. {$remindersSent} rappel(s) envoyé(s).");
    }

    // -------------------------------------------------------------------------
    // Anti-doublon
    // -------------------------------------------------------------------------

    /**
     * Vérifie si un rappel a déjà été envoyé aujourd'hui pour cette clé.
     */
    private function hasReminderBeenSentToday(string $key): bool
    {
        $lastSent = $this->setting->get($key);
        if ($lastSent === null) {
            return false;
        }

        return date('Y-m-d') === substr($lastSent, 0, 10);
    }

    /**
     * Enregistre la date d'envoi du rappel pour cette clé.
     */
    private function markReminderSent(string $key): void
    {
        $this->setting->set($key, date('Y-m-d H:i:s'));
    }

    // -------------------------------------------------------------------------
    // Templates email HTML
    // -------------------------------------------------------------------------

    /**
     * Construit le corps HTML pour un rappel d'abonnement.
     *
     * @param array<string, mixed> $sub
     */
    private function buildSubscriptionEmailBody(array $sub, int $daysLeft, int $threshold): string
    {
        $urgencyColor  = $daysLeft <= 7 ? '#E53935' : ($daysLeft <= 14 ? '#F57C00' : '#E8A000');
        $urgencyLabel  = $daysLeft <= 7 ? 'URGENT' : ($daysLeft <= 14 ? 'Attention' : 'Rappel');
        $name          = htmlspecialchars($sub['name'] ?? 'Inconnu', ENT_QUOTES);
        $status        = htmlspecialchars(ucfirst($sub['status'] ?? 'inconnu'), ENT_QUOTES);
        $expiresAt     = htmlspecialchars($sub['expires_at'] ?? 'Non renseigné', ENT_QUOTES);
        $price         = $sub['price'] !== null ? htmlspecialchars((string) $sub['price'], ENT_QUOTES) . ' ' . htmlspecialchars($sub['currency'] ?? 'EUR', ENT_QUOTES) : 'N/A';
        $period        = htmlspecialchars($sub['billing_period'] ?? 'N/A', ENT_QUOTES);

        return $this->wrapEmailTemplate(
            "{$urgencyLabel} — Abonnement Hostinger expirant bientôt",
            $urgencyColor,
            <<<HTML
            <h2 style="color:{$urgencyColor};margin:0 0 16px;">⚠️ {$urgencyLabel} — Expiration dans <strong>{$daysLeft} jour(s)</strong></h2>
            <p style="color:#d1d5db;font-size:15px;">
                L'abonnement Hostinger suivant expire dans <strong style="color:#fff;">{$daysLeft} jour(s)</strong>.
                Veuillez renouveler votre abonnement dès que possible pour éviter toute interruption de service.
            </p>
            <table style="width:100%;border-collapse:collapse;margin:24px 0;">
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;width:160px;">Plan</td>
                    <td style="padding:12px 0;color:#fff;font-size:15px;font-weight:600;">{$name}</td>
                </tr>
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Statut</td>
                    <td style="padding:12px 0;color:#fff;">{$status}</td>
                </tr>
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Expiration</td>
                    <td style="padding:12px 0;color:{$urgencyColor};font-weight:700;">{$expiresAt}</td>
                </tr>
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Prix</td>
                    <td style="padding:12px 0;color:#fff;">{$price}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Fréquence</td>
                    <td style="padding:12px 0;color:#fff;">{$period}</td>
                </tr>
            </table>
            <a href="https://hpanel.hostinger.com/billing" style="display:inline-block;background:{$urgencyColor};color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;font-size:15px;">
                Renouveler maintenant →
            </a>
            HTML
        );
    }

    /**
     * Construit le corps HTML pour un rappel de domaine.
     *
     * @param array<string, mixed> $domain
     */
    private function buildDomainEmailBody(array $domain, int $daysLeft, int $threshold): string
    {
        $urgencyColor = $daysLeft <= 7 ? '#E53935' : ($daysLeft <= 14 ? '#F57C00' : '#E8A000');
        $urgencyLabel = $daysLeft <= 7 ? 'URGENT' : ($daysLeft <= 14 ? 'Attention' : 'Rappel');
        $name         = htmlspecialchars($domain['domain'] ?? 'inconnu', ENT_QUOTES);
        $status       = htmlspecialchars(ucfirst($domain['status'] ?? 'inconnu'), ENT_QUOTES);
        $expiresAt    = htmlspecialchars($domain['expires_at'] ?? 'Non renseigné', ENT_QUOTES);
        $autoRenew    = $domain['auto_renew'] ? 'Activé' : 'Désactivé';

        return $this->wrapEmailTemplate(
            "{$urgencyLabel} — Domaine Hostinger expirant bientôt",
            $urgencyColor,
            <<<HTML
            <h2 style="color:{$urgencyColor};margin:0 0 16px;">⚠️ {$urgencyLabel} — Domaine expirant dans <strong>{$daysLeft} jour(s)</strong></h2>
            <p style="color:#d1d5db;font-size:15px;">
                Le nom de domaine <strong style="color:#fff;">{$name}</strong> expire dans
                <strong style="color:{$urgencyColor};">{$daysLeft} jour(s)</strong>.
                Renouvelez-le rapidement pour éviter de perdre votre domaine.
            </p>
            <table style="width:100%;border-collapse:collapse;margin:24px 0;">
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;width:160px;">Domaine</td>
                    <td style="padding:12px 0;color:#fff;font-size:15px;font-weight:600;">{$name}</td>
                </tr>
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Statut</td>
                    <td style="padding:12px 0;color:#fff;">{$status}</td>
                </tr>
                <tr style="border-bottom:1px solid #374151;">
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Expiration</td>
                    <td style="padding:12px 0;color:{$urgencyColor};font-weight:700;">{$expiresAt}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0;color:#9ca3af;font-size:13px;">Renouvellement auto</td>
                    <td style="padding:12px 0;color:#fff;">{$autoRenew}</td>
                </tr>
            </table>
            <a href="https://hpanel.hostinger.com/domains" style="display:inline-block;background:{$urgencyColor};color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;font-size:15px;">
                Renouveler le domaine →
            </a>
            HTML
        );
    }

    /**
     * Enveloppe le contenu email dans un template HTML responsive aux couleurs ATLÉX-SPORT.
     */
    private function wrapEmailTemplate(string $title, string $accentColor, string $body): string
    {
        $year = date('Y');
        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$title}</title>
        </head>
        <body style="margin:0;padding:0;background-color:#0a0e1a;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#0a0e1a;padding:40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" border="0"
                               style="max-width:600px;width:100%;background-color:#001a3d;border-radius:12px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;">
                            <!-- En-tête -->
                            <tr>
                                <td style="background-color:#001a3d;padding:28px 32px;border-bottom:3px solid {$accentColor};">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="vertical-align:middle;">
                                                <span style="font-family:Georgia,serif;font-size:22px;font-weight:700;color:#fff;letter-spacing:2px;">
                                                    ATL<span style="color:#D7B899;">É</span>X-SPORT
                                                </span>
                                            </td>
                                            <td align="right" style="vertical-align:middle;">
                                                <span style="background:{$accentColor};color:#fff;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:700;">
                                                    HOSTINGER MONITORING
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- Corps -->
                            <tr>
                                <td style="padding:32px;">
                                    {$body}
                                </td>
                            </tr>
                            <!-- Pied de page -->
                            <tr>
                                <td style="padding:24px 32px;border-top:1px solid rgba(255,255,255,0.08);background-color:#000d1f;">
                                    <p style="margin:0;color:#4b5563;font-size:12px;text-align:center;">
                                        ATLÉX-SPORT — Administration &bull; Cet email a été envoyé automatiquement.<br>
                                        &copy; {$year} ATLÉX-SPORT. Tous droits réservés.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        HTML;
    }

    // -------------------------------------------------------------------------
    // Logging
    // -------------------------------------------------------------------------

    /**
     * Écrit un message dans le fichier de log.
     */
    private function log(string $message): void
    {
        $line = sprintf("[%s] [HostingerReminder] %s\n", date('Y-m-d H:i:s'), $message);

        // ROOT est défini dans public/index.php
        $logDir = defined('ROOT') ? ROOT . '/storage/logs' : __DIR__ . '/../../storage/logs';
        @file_put_contents($logDir . '/hostinger.log', $line, FILE_APPEND | LOCK_EX);

        // Sortie console (utile pour le cron)
        echo $line;
    }
}
