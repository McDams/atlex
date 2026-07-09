<?php

declare(strict_types=1);

namespace App\Services;

use GeoIp2\Database\Reader;
use RuntimeException;
use Throwable;

/**
 * Géolocalisation IP → pays/ville via une base locale MaxMind GeoLite2-City.
 *
 * La base (storage/geoip/GeoLite2-City.mmdb) n'est pas versionnée : elle est
 * téléchargée par cron/geoip_update.php à partir de MAXMIND_LICENSE_KEY.
 * Tant qu'elle est absente, locate() dégrade proprement en renvoyant des
 * valeurs nulles plutôt que d'échouer — le suivi de visites continue de
 * fonctionner sans géolocalisation.
 */
final class GeoIpService
{
    private static ?Reader $reader = null;
    private static bool $unavailable = false;

    /**
     * @return array<string,string|float|null>
     */
    public function locate(?string $ip): array
    {
        $empty = [
            'country_code'   => null,
            'country_name'   => null,
            'continent_code' => null,
            'continent_name' => null,
            'city_name'      => null,
            'latitude'       => null,
            'longitude'      => null,
        ];

        if ($ip === null || $ip === '') {
            return $empty;
        }

        // Les IP privées/réservées (LAN, localhost...) ne sont jamais dans la base.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $empty;
        }

        try {
            $record = $this->reader()->city($ip);
        } catch (Throwable) {
            return $empty;
        }

        return [
            'country_code'   => $record->country->isoCode,
            'country_name'   => $record->country->names['fr'] ?? $record->country->name,
            'continent_code' => $record->continent->code,
            'continent_name' => $record->continent->names['fr'] ?? $record->continent->name,
            'city_name'      => $record->city->names['fr'] ?? $record->city->name,
            'latitude'       => $record->location->latitude,
            'longitude'      => $record->location->longitude,
        ];
    }

    private function reader(): Reader
    {
        if (self::$reader instanceof Reader) {
            return self::$reader;
        }

        $path = ROOT . '/storage/geoip/GeoLite2-City.mmdb';

        if (self::$unavailable || !is_file($path)) {
            self::$unavailable = true;
            throw new RuntimeException('Base GeoLite2 introuvable (' . $path . ').');
        }

        return self::$reader = new Reader($path, ['fr', 'en']);
    }
}
