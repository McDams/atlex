<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\MomoCollectionsService;
use PHPUnit\Framework\TestCase;

final class MomoCollectionsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['MOMO_SUBSCRIPTION_KEY', 'MOMO_API_USER', 'MOMO_API_KEY'] as $key) {
            putenv($key);
            unset($_ENV[$key]);
        }
    }

    public function testIsConfiguredIsFalseWithoutAllThreeCredentials(): void
    {
        $service = new MomoCollectionsService();
        $this->assertFalse($service->isConfigured());

        putenv('MOMO_SUBSCRIPTION_KEY=key');
        putenv('MOMO_API_USER=user');
        // MOMO_API_KEY toujours absent
        $service = new MomoCollectionsService();
        $this->assertFalse($service->isConfigured());
    }

    public function testIsConfiguredIsTrueWithAllCredentials(): void
    {
        putenv('MOMO_SUBSCRIPTION_KEY=key');
        putenv('MOMO_API_USER=user');
        putenv('MOMO_API_KEY=secret');

        $service = new MomoCollectionsService();
        $this->assertTrue($service->isConfigured());
    }

    public function testNormalizePhoneHandlesCommonBeninFormats(): void
    {
        $service = new MomoCollectionsService();
        $method = new \ReflectionMethod($service, 'normalizePhone');
        $method->setAccessible(true);

        // Numérotation béninoise post-2021 : numéro local à 10 chiffres,
        // préfixe "01" conservé tel quel après l'indicatif pays (229).
        $this->assertSame('2290192573333', $method->invoke($service, '+229 01 92 57 33 33'));
        $this->assertSame('2290192573333', $method->invoke($service, '00229 01 92 57 33 33'));
        $this->assertSame('2290192573333', $method->invoke($service, '0192573333'));
        $this->assertSame('2290192573333', $method->invoke($service, '229 01 92 57 33 33'));
    }

    public function testGenerateUuidProducesValidV4Format(): void
    {
        $service = new MomoCollectionsService();
        $method = new \ReflectionMethod($service, 'generateUuid');
        $method->setAccessible(true);

        $uuid = $method->invoke($service);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}
