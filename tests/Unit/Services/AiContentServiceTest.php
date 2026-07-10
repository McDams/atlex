<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AiContentService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AiContentServiceTest extends TestCase
{
    public function testIsConfiguredIsFalseWithoutApiKey(): void
    {
        $service = new AiContentService('');

        $this->assertFalse($service->isConfigured());
    }

    public function testIsConfiguredIsTrueWithApiKey(): void
    {
        $service = new AiContentService('gemini-test-key');

        $this->assertTrue($service->isConfigured());
    }

    public function testDraftThrowsWhenApiKeyMissing(): void
    {
        $service = new AiContentService('');

        $this->expectException(RuntimeException::class);
        $service->draft('Tu es un assistant.', 'Rédige un post.');
    }

    public function testFallsBackToEnvironmentVariableWhenNoKeyProvided(): void
    {
        putenv('GEMINI_API_KEY=from-env-test');
        $service = new AiContentService();

        $this->assertTrue($service->isConfigured());

        putenv('GEMINI_API_KEY'); // nettoyage
    }
}
