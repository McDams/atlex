<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Tâches internes (espace Secrétaire Général).
 */
final class Task extends BaseModel
{
    protected string $table = 'tasks';

    protected array $fillable = [
        'title', 'description', 'status', 'priority',
        'due_date', 'assigned_to', 'created_by',
    ];

    public const STATUSES = ['a_faire', 'en_cours', 'termine'];

    /**
     * Toutes les tâches groupées par statut (pour le Kanban).
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public function groupedByStatus(): array
    {
        $grouped = ['a_faire' => [], 'en_cours' => [], 'termine' => []];

        $rows = $this->db->query(
            "SELECT * FROM {$this->table}
             ORDER BY FIELD(priority,'urgente','haute','normale','basse'), due_date ASC"
        )->fetchAll();

        foreach ($rows as $row) {
            $grouped[(string) $row['status']][] = $row;
        }

        return $grouped;
    }

    /**
     * Tâches récentes pour le tableau de bord.
     *
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countInProgress(): int
    {
        return $this->count(['status' => 'en_cours']);
    }

    /**
     * Met à jour uniquement le statut d'une tâche.
     */
    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        return $this->update($id, ['status' => $status]);
    }
}
