<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Catégories d'événements.
 */
final class EventCategory extends BaseModel
{
    protected string $table = 'event_categories';

    protected array $fillable = [
        'slug', 'name', 'description', 'icon', 'color', 'sort_order', 'is_active',
    ];

    /**
     * Toutes les catégories actives, triées par sort_order.
     *
     * @return array<int,array<string,mixed>>
     */
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE is_active = 1
             ORDER BY sort_order ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Toutes les catégories (pour l'admin), triées.
     *
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        $stmt = $this->db->query(
            "SELECT ec.*,
                    COUNT(e.id) AS event_count
             FROM {$this->table} ec
             LEFT JOIN events e ON e.category_id = ec.id
             GROUP BY ec.id
             ORDER BY ec.sort_order ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Cherche par slug.
     *
     * @return array<string,mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Retourne un tableau slug => name pour les selects.
     *
     * @return array<string,string>
     */
    public function forSelect(): array
    {
        $rows = $this->allActive();
        $out  = [];
        foreach ($rows as $row) {
            $out[(string) $row['id']] = (string) $row['name'];
        }
        return $out;
    }
}
