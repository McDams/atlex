<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Comptes réseaux sociaux connectés (un par plateforme).
 */
final class SocialAccount extends BaseModel
{
    protected string $table = 'social_accounts';

    protected array $fillable = [
        'platform', 'label', 'access_token', 'account_ref', 'token_expires_at', 'is_active',
    ];

    /**
     * @return array<string,mixed>|null
     */
    public function findByPlatform(string $platform): ?array
    {
        return $this->findBy('platform', $platform);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->findAll('platform', 'ASC');
    }

    /**
     * Enregistre (crée ou remplace) le compte d'une plateforme.
     *
     * @param array<string,mixed> $data
     */
    public function upsertForPlatform(string $platform, array $data): void
    {
        $existing = $this->findByPlatform($platform);
        $data['platform'] = $platform;

        if ($existing !== null) {
            $this->update((int) $existing['id'], $data);
            return;
        }

        $this->create($data);
    }
}
