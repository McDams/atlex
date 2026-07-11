<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\MatchCardImageGenerator;
use PHPUnit\Framework\TestCase;

final class MatchCardImageGeneratorTest extends TestCase
{
    public function testGeneratesWellFormedSvg(): void
    {
        $svg = (new MatchCardImageGenerator())->generate(
            'Brésil',
            'Maroc',
            1,
            1,
            'Coupe du Monde de la FIFA 2026',
            'Dim. 14/06',
            [
                ['team' => 'away', 'scorer' => 'Ismael Saibari', 'minute' => 21],
                ['team' => 'home', 'scorer' => 'Vinícius Júnior', 'minute' => 32],
            ]
        );

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($svg);

        $this->assertNotFalse($doc, 'Le SVG généré doit être un XML bien formé.');
        $this->assertStringContainsString('Brésil', $svg);
        $this->assertStringContainsString('Maroc', $svg);
        $this->assertStringContainsString('1 – 1', $svg);
        $this->assertStringContainsString('Vinícius Júnior', $svg);
    }

    public function testEscapesTeamNamesToPreventXmlInjection(): void
    {
        $svg = (new MatchCardImageGenerator())->generate(
            'Team <script>alert(1)</script>',
            'Away & Co',
            2,
            0,
            'Test',
            '01/01',
            []
        );

        $this->assertStringNotContainsString('<script>', $svg);

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($svg);
        $this->assertNotFalse($doc, 'Les caractères spéciaux dans les noms doivent rester un XML valide.');
    }

    public function testHandlesNoGoalEventsGracefully(): void
    {
        $svg = (new MatchCardImageGenerator())->generate('A', 'B', 0, 0, 'Test', '01/01', []);

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($svg);
        $this->assertNotFalse($doc);
    }

    public function testSavesFileToUploadsMatchesDirectory(): void
    {
        $svg = (new MatchCardImageGenerator())->generate('A', 'B', 1, 0, 'Test', '01/01', []);
        $relativePath = (new MatchCardImageGenerator())->save($svg, 'a-b');

        $this->assertStringStartsWith('uploads/matches/', $relativePath);
        $this->assertStringEndsWith('.svg', $relativePath);
        $this->assertFileExists(ROOT . '/public/' . $relativePath);

        unlink(ROOT . '/public/' . $relativePath);
    }
}
