<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Événements et calendrier.
 */
final class Event extends BaseModel
{
    protected string $table = 'events';

    protected array $fillable = [
        'title', 'slug', 'type', 'discipline', 'category_id', 'description',
        'start_datetime', 'end_datetime', 'location', 'is_published',
    ];

    /**
     * Prochains événements publiés (avec catégorie jointe).
     *
     * @return array<int,array<string,mixed>>
     */
    public function upcoming(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             WHERE e.is_published = 1 AND e.start_datetime >= NOW()
             ORDER BY e.start_datetime ASC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countUpcoming(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE is_published = 1 AND start_datetime >= NOW()"
        );

        return (int) $stmt->fetchColumn();
    }

    /**
     * Événements d'un mois donné (pour le calendrier), avec catégorie.
     *
     * @return array<int,array<string,mixed>>
     */
    public function forMonth(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             WHERE e.is_published = 1
               AND YEAR(e.start_datetime) = :y
               AND MONTH(e.start_datetime) = :m
             ORDER BY e.start_datetime ASC"
        );
        $stmt->execute(['y' => $year, 'm' => $month]);

        return $stmt->fetchAll();
    }

    /**
     * Événements filtrés par catégorie (slug).
     *
     * @return array<int,array<string,mixed>>
     */
    public function byCategory(int $categoryId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             WHERE e.is_published = 1 AND e.category_id = :cat
             ORDER BY e.start_datetime DESC
             LIMIT :limit"
        );
        $stmt->bindValue('cat', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        $stmt = $this->db->query(
            "SELECT e.*, ec.name AS category_name, ec.color AS category_color
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             ORDER BY e.start_datetime DESC"
        );
        return $stmt->fetchAll();
    }
}
