<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    public function testKeepsAllowedFormattingTags(): void
    {
        $out = HtmlSanitizer::clean('<p>Bonjour <strong>ATLEX</strong></p>');

        $this->assertSame('<p>Bonjour <strong>ATLEX</strong></p>', $out);
    }

    public function testStripsScriptTagAndItsContent(): void
    {
        $out = HtmlSanitizer::clean('<p>Texte</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('alert', $out);
    }

    public function testRemovesEventHandlerAttributeRegardlessOfQuoting(): void
    {
        // Espace avant le "=" : contournait l'ancien filtre regex.
        $out = HtmlSanitizer::clean('<img src="x.jpg" onerror ="alert(1)">');

        $this->assertStringNotContainsString('onerror', $out);
    }

    public function testRemovesUnquotedEventHandlerAttribute(): void
    {
        $out = HtmlSanitizer::clean('<img src=x.jpg onerror=alert(1)>');

        $this->assertStringNotContainsString('onerror', $out);
    }

    public function testRejectsJavascriptSchemeInLinks(): void
    {
        $out = HtmlSanitizer::clean('<a href="javascript:alert(1)">clic</a>');

        $this->assertStringNotContainsString('javascript:', $out);
    }

    public function testUnwrapsDisallowedTagButKeepsSafeContent(): void
    {
        $out = HtmlSanitizer::clean('<div><p>Contenu</p></div>');

        $this->assertStringNotContainsString('<div>', $out);
        $this->assertStringContainsString('<p>Contenu</p>', $out);
    }

    public function testDropsAttributesNotOnTheWhitelist(): void
    {
        $out = HtmlSanitizer::clean('<p style="background:url(javascript:alert(1))" class="ok">Texte</p>');

        $this->assertStringNotContainsString('style=', $out);
    }

    public function testAddsNoopenerOnBlankTargetLinks(): void
    {
        $out = HtmlSanitizer::clean('<a href="https://example.com" target="_blank">lien</a>');

        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $out);
    }

    public function testAllowsRelativeAndHttpsUrls(): void
    {
        $out = HtmlSanitizer::clean('<a href="/actualites/mon-article">interne</a><a href="https://example.com">externe</a>');

        $this->assertStringContainsString('href="/actualites/mon-article"', $out);
        $this->assertStringContainsString('href="https://example.com"', $out);
    }

    public function testEmptyInputReturnsEmptyString(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(''));
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }
}
