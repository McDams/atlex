<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Sources de veille de financements (flux RSS ou requêtes Google Actualités).
 */
final class FundingSource extends BaseModel
{
    protected string $table = 'funding_sources';

    protected array $fillable = ['name', 'type', 'url', 'query', 'is_active'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY is_active DESC, name ASC"
        )->fetchAll();
    }

    /**
     * Sources actives uniquement.
     *
     * @return array<int,array<string,mixed>>
     */
    public function active(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC"
        )->fetchAll();
    }

    public function toggleActive(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function touchFetched(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_fetch_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
