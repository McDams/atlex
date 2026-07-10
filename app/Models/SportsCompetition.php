<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Compétitions sportives suivies pour la génération de résumés de matchs.
 */
final class SportsCompetition extends BaseModel
{
    protected string $table = 'sports_competitions';

    protected array $fillable = ['name', 'external_competition_id', 'is_active'];

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

    public function touchChecked(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_checked_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
