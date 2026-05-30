<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Membres de l'association.
 */
final class Member extends BaseModel
{
    protected string $table = 'members';

    protected array $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'age',
        'gender', 'discipline', 'status', 'joined_at', 'notes',
    ];

    /**
     * Recherche les membres par nom/prénom/email.
     *
     * @return array<int,array<string,mixed>>
     */
    public function search(string $term): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE first_name LIKE :t OR last_name LIKE :t OR email LIKE :t
             ORDER BY last_name ASC, first_name ASC"
        );
        $stmt->execute(['t' => '%' . $term . '%']);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function byDiscipline(string $discipline): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE discipline = :d ORDER BY last_name ASC"
        );
        $stmt->execute(['d' => $discipline]);

        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        return $this->count(['status' => 'actif']);
    }

    /**
     * Nombre de membres actifs par discipline.
     *
     * @return array<string,int>
     */
    public function statsByDiscipline(): array
    {
        $rows = $this->db->query(
            "SELECT discipline, COUNT(*) AS total
             FROM {$this->table}
             WHERE status = 'actif'
             GROUP BY discipline"
        )->fetchAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[(string) $row['discipline']] = (int) $row['total'];
        }

        return $stats;
    }
}
