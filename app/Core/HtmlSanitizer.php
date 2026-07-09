<?php

declare(strict_types=1);

namespace App\Core;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Nettoyage du HTML riche (articles, communiqués…) saisi via l'éditeur
 * WYSIWYG admin, avant stockage en base.
 *
 * Contrairement à un filtre par expressions régulières (contournable par des
 * attributs non cités, des espaces autour du `=`, des URI encodées…), ce
 * nettoyeur analyse un véritable arbre DOM : seules les balises et attributs
 * explicitement autorisés survivent, tout le reste — y compris n'importe
 * quel gestionnaire d'évènement `on*` ou schéma d'URL dangereux — est éliminé
 * par construction plutôt que détecté au cas par cas.
 */
final class HtmlSanitizer
{
    /** @var array<string,array<int,string>> Balise => attributs autorisés. */
    private const ALLOWED_TAGS = [
        'p'          => ['class'],
        'br'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'h1'         => [],
        'h2'         => [],
        'h3'         => [],
        'h4'         => [],
        'blockquote' => [],
        'ul'         => [],
        'ol'         => ['start'],
        'li'         => [],
        'a'          => ['href', 'title', 'target'],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tr'         => [],
        'td'         => ['colspan', 'rowspan'],
        'th'         => ['colspan', 'rowspan'],
        'img'        => ['src', 'alt', 'width', 'height'],
        'hr'         => [],
    ];

    /** @var array<int,string> Balises dont le contenu est entièrement supprimé (pas seulement dépouillé). */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form',
        'input', 'button', 'select', 'textarea', 'svg', 'math', 'noscript', 'template',
    ];

    private const ALLOWED_URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="__root__">' . $html . '</div>';

        libxml_use_internal_errors(true);
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $dom->getElementById('__root__');
        if ($root === null) {
            return '';
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMText) {
                continue;
            }

            if (!$node instanceof DOMElement) {
                $parent->removeChild($node);
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($node);
                continue;
            }

            if (!array_key_exists($tag, self::ALLOWED_TAGS)) {
                // Balise non autorisée : on garde son contenu (nettoyé), on retire l'enveloppe.
                self::sanitizeChildren($node);
                while ($node->firstChild !== null) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                continue;
            }

            self::sanitizeAttributes($node, self::ALLOWED_TAGS[$tag]);
            self::sanitizeChildren($node);
        }
    }

    /**
     * @param array<int,string> $allowedAttrs
     */
    private static function sanitizeAttributes(DOMElement $node, array $allowedAttrs): void
    {
        foreach (iterator_to_array($node->attributes ?? []) as $attr) {
            $name = strtolower($attr->nodeName);

            if (!in_array($name, $allowedAttrs, true)) {
                $node->removeAttribute($attr->nodeName);
                continue;
            }

            if (($name === 'href' || $name === 'src') && !self::isSafeUrl($attr->nodeValue)) {
                $node->removeAttribute($attr->nodeName);
            }
        }

        // Neutralise le tabnabbing sur les liens ouverts dans un nouvel onglet.
        if (strtolower($node->tagName) === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        // Chemins relatifs ou ancres : aucun schéma à valider.
        if ($url[0] === '/' || $url[0] === '#' || $url[0] === '?') {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === null) {
            return true;
        }

        return in_array(strtolower($scheme), self::ALLOWED_URL_SCHEMES, true);
    }
}
