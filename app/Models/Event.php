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
        'title', 'slug', 'type', 'discipline', 'description',
        'start_datetime', 'end_datetime', 'location', 'is_published',
    ];

    /**
     * Prochains événements publiés.
     *
     * @return array<int,array<string,mixed>>
     */
    public function upcoming(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE is_published = 1 AND start_datetime >= NOW()
             ORDER BY start_datetime ASC
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
     * Événements d'un mois donné (pour le calendrier).
     *
     * @return array<int,array<string,mixed>>
     */
    public function forMonth(int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE is_published = 1
               AND YEAR(start_datetime) = :y
               AND MONTH(start_datetime) = :m
             ORDER BY start_datetime ASC"
        );
        $stmt->execute(['y' => $year, 'm' => $month]);

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
        return $this->findAll('start_datetime', 'DESC');
    }
}
