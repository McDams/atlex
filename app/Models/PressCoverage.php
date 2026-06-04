<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Revue de presse : articles externes parlant de l'association (Centre média).
 */
final class PressCoverage extends BaseModel
{
    protected string $table = 'press_coverage';

    protected array $fillable = ['title', 'media_name', 'url', 'published_date', 'sort_order'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY published_date IS NULL, published_date DESC, sort_order ASC"
        )->fetchAll();
    }
}
