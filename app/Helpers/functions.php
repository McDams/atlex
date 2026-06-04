<?php

/**
 * Helpers globaux disponibles dans toute l'application.
 */

if (!function_exists('e')) {
    /**
     * Échappe une valeur pour un affichage HTML sécurisé.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Construit une URL absolue à partir d'un chemin relatif.
     */
    function url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Raccourci vers un asset public.
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige vers un chemin interne puis arrête l'exécution.
     */
    function redirect(string $path): never
    {
        $location = str_starts_with($path, 'http') ? $path : url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('flash')) {
    /**
     * Définit (avec $msg) ou récupère et consomme (sans $msg) un message flash.
     */
    function flash(string $key, ?string $msg = null): ?string
    {
        if ($msg !== null) {
            $_SESSION['_flash'][$key] = $msg;
            return null;
        }

        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }

        return null;
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne valeur de formulaire (repopulation après erreur).
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('set_old')) {
    /**
     * Mémorise les anciennes entrées de formulaire pour le prochain rendu.
     */
    function set_old(array $data): void
    {
        $_SESSION['_old'] = $data;
    }
}

if (!function_exists('clear_old')) {
    function clear_old(): void
    {
        unset($_SESSION['_old']);
    }
}

if (!function_exists('set_errors')) {
    /**
     * Mémorise les erreurs de validation par champ pour le prochain rendu.
     *
     * @param array<string,array<int,string>> $errors
     */
    function set_errors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }
}

if (!function_exists('errors_all')) {
    /**
     * Récupère toutes les erreurs de validation mémorisées.
     *
     * @return array<string,array<int,string>>
     */
    function errors_all(): array
    {
        return $_SESSION['_errors'] ?? [];
    }
}

if (!function_exists('clear_errors')) {
    function clear_errors(): void
    {
        unset($_SESSION['_errors']);
    }
}

if (!function_exists('slugify')) {
    /**
     * Transforme un titre en slug URL-safe.
     */
    function slugify(string $text): string
    {
        // Translittération des accents : intl si dispo, sinon iconv, sinon brut.
        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: $text;
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');

        return $text !== '' ? $text : 'item-' . substr(md5((string) microtime(true)), 0, 8);
    }
}

if (!function_exists('format_date_fr')) {
    /**
     * Formate une date au format français lisible.
     */
    function format_date_fr(?string $datetime, bool $withTime = false): string
    {
        if (empty($datetime)) {
            return '';
        }

        $months = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        $ts = strtotime($datetime);
        if ($ts === false) {
            return e($datetime);
        }

        $formatted = (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($withTime) {
            $formatted .= ' à ' . date('H\hi', $ts);
        }

        return $formatted;
    }
}

if (!function_exists('discipline_label')) {
    /**
     * Retourne le libellé lisible d'une discipline.
     */
    function discipline_label(?string $key): string
    {
        $labels = [
            'football'      => 'Football',
            'basketball'    => 'Basketball',
            'handball'      => 'Handball',
            'arts_martiaux' => 'Arts Martiaux',
            'tous'          => 'Toutes disciplines',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('format_fcfa')) {
    /**
     * Formate un montant en francs CFA (séparateur de milliers par espace).
     */
    function format_fcfa(mixed $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return number_format((float) $amount, 0, ',', ' ') . ' FCFA';
    }
}

if (!function_exists('project_status_label')) {
    /**
     * Libellé lisible d'un statut de projet.
     */
    function project_status_label(?string $key): string
    {
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'en_pause' => 'En pause',
            'termine'  => 'Terminé',
            'annule'   => 'Annulé',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('funding_status_label')) {
    /**
     * Libellé lisible d'un statut de candidature de financement.
     */
    function funding_status_label(?string $key): string
    {
        $labels = [
            'identifie'      => 'Identifié',
            'en_preparation' => 'En préparation',
            'depose'         => 'Déposé',
            'obtenu'         => 'Obtenu',
            'refuse'         => 'Refusé',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('funding_type_label')) {
    /**
     * Libellé lisible d'un type de financement.
     */
    function funding_type_label(?string $key): string
    {
        $labels = [
            'subvention'   => 'Subvention',
            'appel_projet' => 'Appel à projets',
            'sponsoring'   => 'Sponsoring',
            'crowdfunding' => 'Crowdfunding / dons communautaires',
            'don'          => 'Don',
            'bourse'       => 'Bourse',
            'prix'         => 'Prix',
            'autre'        => 'Autre',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('funding_checklist_steps')) {
    /**
     * Étapes de démarches par défaut pour candidater à un financement.
     * Surchargeable via le paramètre `funding_checklist_template` (une étape par ligne).
     *
     * @return array<int,string>
     */
    function funding_checklist_steps(): array
    {
        $default = [
            "Lire l'appel et vérifier l'éligibilité de l'association",
            'Constituer le dossier administratif (statuts, RIB, rapport d\'activité)',
            "Rédiger la note d'intention / lettre de motivation",
            'Établir le budget prévisionnel du projet',
            'Faire valider le dossier par le bureau',
            'Déposer le dossier avant la date limite',
            'Assurer le suivi et relancer le bailleur',
        ];

        $tpl = (new \App\Models\Setting())->get('funding_checklist_template');
        if ($tpl === null || trim($tpl) === '') {
            return $default;
        }

        $lines = array_values(array_filter(array_map(
            static fn (string $l): string => trim($l),
            preg_split('/\r\n|\r|\n/', $tpl) ?: []
        ), static fn (string $l): bool => $l !== ''));

        return $lines !== [] ? $lines : $default;
    }
}

if (!function_exists('sponsor_tier_label')) {
    /**
     * Libellé lisible d'un niveau de partenaire.
     */
    function sponsor_tier_label(?string $key): string
    {
        $labels = [
            'officiel' => 'Partenaire officiel',
            'associe'  => 'Partenaire associé',
            'media'    => 'Partenaire média',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('news_category_label')) {
    /**
     * Retourne le libellé lisible d'une catégorie d'actualité.
     */
    function news_category_label(?string $key): string
    {
        $labels = [
            'general'     => 'Général',
            'resultat'    => 'Résultat',
            'recrutement' => 'Recrutement',
            'evenement'   => 'Événement',
            'partenariat' => 'Partenariat',
            'rapport'     => "Rapports d'activité",
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Génère le champ caché CSRF pour les formulaires.
     */
    function csrf_field(): string
    {
        $token = \App\Core\CSRF::generateToken();
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Génère un champ caché pour l'override de méthode HTTP.
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('responsive_image')) {
    /**
     * Génère une balise <picture> servant le WebP s'il existe (avec repli sur
     * l'image d'origine), et applique le lazy-loading par défaut.
     *
     * @param string               $path  chemin relatif sous assets/ (ex: 'images/hero-bg.png')
     * @param array<string,mixed>  $opts  eager (bool), width (int), height (int)
     */
    function responsive_image(string $path, string $alt, string $class = '', array $opts = []): string
    {
        $eager = !empty($opts['eager']);

        $attrs  = ' class="' . e($class) . '" alt="' . e($alt) . '"';
        $attrs .= $eager ? ' fetchpriority="high" decoding="async"' : ' loading="lazy" decoding="async"';
        if (!empty($opts['width'])) {
            $attrs .= ' width="' . (int) $opts['width'] . '"';
        }
        if (!empty($opts['height'])) {
            $attrs .= ' height="' . (int) $opts['height'] . '"';
        }

        $img = '<img src="' . asset($path) . '"' . $attrs . '>';

        $webpRel = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path) ?? $path;
        $webpFs  = ROOT . '/public/assets/' . ltrim($webpRel, '/');

        if ($webpRel !== $path && is_file($webpFs)) {
            return '<picture><source srcset="' . asset($webpRel) . '" type="image/webp">' . $img . '</picture>';
        }

        return $img;
    }
}

if (!function_exists('safe_url')) {
    /**
     * Valide qu'une URL externe utilise le schéma http(s).
     * Retourne l'URL si elle est sûre, sinon null (neutralise javascript:, data:, etc.).
     */
    function safe_url(?string $url): ?string
    {
        $url = trim((string) ($url ?? ''));
        if ($url === '') {
            return null;
        }

        return preg_match('#^https?://#i', $url) === 1 ? $url : null;
    }
}

if (!function_exists('youtube_embed_url')) {
    /**
     * Transforme un lien YouTube (watch, youtu.be, shorts) en URL d'intégration
     * « privacy-enhanced » (youtube-nocookie). Retourne null si l'URL n'est pas
     * reconnue comme une vidéo YouTube intégrable.
     */
    function youtube_embed_url(string $url): ?string
    {
        $patterns = [
            '#youtu\.be/([A-Za-z0-9_-]{11})#',
            '#youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#',
            '#youtube\.com/embed/([A-Za-z0-9_-]{11})#',
            '#youtube\.com/shorts/([A-Za-z0-9_-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return 'https://www.youtube-nocookie.com/embed/' . $m[1];
            }
        }

        return null;
    }
}

if (!function_exists('email_template')) {
    /**
     * Enveloppe un contenu HTML dans un gabarit d'email brandé ATLEX - Sport.
     * Le titre est échappé ; le corps est du HTML de confiance (construit par
     * l'application, avec e() appliqué sur les valeurs utilisateur).
     */
    function email_template(string $title, string $bodyHtml): string
    {
        $year = date('Y');

        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:24px 0;background:#0a0e1a;font-family:Arial,Helvetica,sans-serif">'
            . '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden">'
            . '<div style="background:#E53935;padding:24px;text-align:center">'
            . '<span style="font-size:24px;font-weight:bold;color:#ffffff;letter-spacing:2px">ATLEX - Sport</span>'
            . '</div>'
            . '<div style="padding:28px;font-size:15px;line-height:1.6;color:#222222">'
            . '<h1 style="font-size:20px;color:#E53935;margin:0 0 16px">' . e($title) . '</h1>'
            . $bodyHtml
            . '</div>'
            . '<div style="background:#0a0e1a;padding:16px;text-align:center;font-size:12px;color:#8a8a8a">'
            . '© ' . $year . ' ATLEX - Sport — Cotonou, Bénin. Là où l\'énergie devient passion.'
            . '</div></div></body></html>';
    }
}
