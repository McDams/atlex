<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Partenaires et sponsors.
 */
final class Sponsor extends BaseModel
{
    protected string $table = 'sponsors';

    protected array $fillable = [
        'name', 'tier', 'logo', 'website_url', 'description', 'is_active', 'sort_order',
    ];

    /**
     * Tous les partenaires ordonnés (espace SG — actifs et masqués).
     *
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(tier,'officiel','associe','media'), sort_order ASC, name ASC"
        )->fetchAll();
    }

    /**
     * Bascule l'état actif/masqué.
     */
    public function toggleActive(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Sponsors actifs ordonnés.
     *
     * @return array<int,array<string,mixed>>
     */
    public function active(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE is_active = 1
             ORDER BY sort_order ASC, name ASC"
        )->fetchAll();
    }

    /**
     * Sponsors actifs groupés par niveau (tier).
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public function groupedByTier(): array
    {
        $grouped = ['officiel' => [], 'associe' => [], 'media' => []];

        foreach ($this->active() as $sponsor) {
            $tier = (string) $sponsor['tier'];
            $grouped[$tier][] = $sponsor;
        }

        return $grouped;
    }
}
