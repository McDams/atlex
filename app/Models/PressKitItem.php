<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Ressources du kit presse téléchargeable (Centre média).
 */
final class PressKitItem extends BaseModel
{
    protected string $table = 'press_kit_items';

    protected array $fillable = ['title', 'description', 'category', 'file', 'sort_order'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(category,'logo','charte','photo','dossier','autre'), sort_order ASC, title ASC"
        )->fetchAll();
    }
}
