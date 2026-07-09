<?php

declare(strict_types=1);

namespace App\Services;

final class GeoIpService
{
    /**
     * @return array<string,string|null>
     */
    public function locate(?string $ip): array
    {
        return [
            'country_code' => null,
            'country_name' => null,
            'region_name' => null,
            'city_name' => null,
            'continent_code' => null,
            'continent_name' => null,
        ];
    }
}