<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Indicateurs d'impact saisis manuellement par le SG.
 */
final class ImpactIndicator extends BaseModel
{
    protected string $table = 'impact_indicators';

    protected array $fillable = ['label', 'value', 'unit', 'sort_order'];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC"
        )->fetchAll();
    }
}
