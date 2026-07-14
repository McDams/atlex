<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\PaypalService;
use PHPUnit\Framework\TestCase;

final class PaypalServiceTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET', 'PAYPAL_WEBHOOK_ID'] as $key) {
            putenv($key);
            unset($_ENV[$key]);
        }
    }

    public function testIsConfiguredIsFalseWithoutCredentials(): void
    {
        $service = new PaypalService();
        $this->assertFalse($service->isConfigured());
    }

    public function testIsConfiguredIsTrueWithClientIdAndSecret(): void
    {
        putenv('PAYPAL_CLIENT_ID=client-id');
        putenv('PAYPAL_CLIENT_SECRET=secret');

        $service = new PaypalService();
        $this->assertTrue($service->isConfigured());
    }

    public function testVerifyWebhookSignatureFailsWithoutWebhookId(): void
    {
        putenv('PAYPAL_CLIENT_ID=client-id');
        putenv('PAYPAL_CLIENT_SECRET=secret');
        // PAYPAL_WEBHOOK_ID volontairement absent

        $service = new PaypalService();
        $this->assertFalse($service->verifyWebhookSignature([], '{}'));
    }
}
