<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\GeoIpService;
use PHPUnit\Framework\TestCase;

final class GeoIpServiceTest extends TestCase
{
    public function testLocateReturnsNullsWhenIpIsNull(): void
    {
        $result = (new GeoIpService())->locate(null);

        $this->assertNull($result['country_code']);
        $this->assertNull($result['city_name']);
        $this->assertNull($result['latitude']);
    }

    public function testLocateReturnsNullsForPrivateIp(): void
    {
        // Une base GeoLite2 ne référence jamais les plages privées/réservées.
        $result = (new GeoIpService())->locate('192.168.1.10');

        $this->assertNull($result['country_code']);
        $this->assertNull($result['country_name']);
    }

    public function testLocateDegradesGracefullyWhenDatabaseFileIsMissing(): void
    {
        // storage/geoip/GeoLite2-City.mmdb n'existe pas dans cet environnement
        // de test : locate() ne doit jamais lever d'exception.
        $result = (new GeoIpService())->locate('8.8.8.8');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('country_code', $result);
        $this->assertArrayHasKey('city_name', $result);
        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);
    }
}
