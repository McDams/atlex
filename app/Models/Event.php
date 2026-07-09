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
        'title',
        'slug',
        'type',
        'discipline',
        'category_id',
        'description',
        'start_datetime',
        'end_datetime',
        'location',
        'is_published',
        'external_uid',
        'source',
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
     * @return array<string,mixed>|null
     */
    public function findByExternalUid(string $uid): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM {$this->table}
             WHERE external_uid = :uid
             LIMIT 1"
        );
        $stmt->execute(['uid' => $uid]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Récupère un événement publié par son identifiant, avec sa catégorie jointe.
     *
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, ec.name AS category_name, ec.slug AS category_slug,
                    ec.color AS category_color, ec.icon AS category_icon
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             WHERE e.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Événements similaires : même catégorie, à venir, en excluant l'événement courant.
     * Repli sur d'autres événements à venir si la catégorie ne fournit pas assez de résultats.
     *
     * @return array<int,array<string,mixed>>
     */
    public function findRelated(int $excludeId, ?int $categoryId, int $limit = 3): array
    {
        $sql = "SELECT e.*, ec.name AS category_name, ec.color AS category_color, ec.icon AS category_icon
                FROM {$this->table} e
                LEFT JOIN event_categories ec ON ec.id = e.category_id
                WHERE e.is_published = 1
                  AND e.id <> :exclude
                  AND e.start_datetime >= NOW()";

        if ($categoryId !== null) {
            $sql .= ' AND e.category_id = :cat';
        }

        $sql .= ' ORDER BY e.start_datetime ASC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('exclude', $excludeId, \PDO::PARAM_INT);

        if ($categoryId !== null) {
            $stmt->bindValue('cat', $categoryId, \PDO::PARAM_INT);
        }

        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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