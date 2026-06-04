<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Projets de l'association (gestion interne SG).
 */
final class Project extends BaseModel
{
    protected string $table = 'projects';

    protected array $fillable = [
        'title', 'description', 'discipline', 'theme', 'status',
        'lead', 'beneficiaries', 'beneficiary_count', 'expected_impact',
        'budget_target', 'start_date', 'end_date', 'created_by',
    ];

    /**
     * Partenaires d'un projet.
     *
     * @return array<int,array<string,mixed>>
     */
    public function partners(int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM project_partners WHERE project_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $projectId]);

        return $stmt->fetchAll();
    }

    /**
     * Remplace en bloc les partenaires d'un projet.
     *
     * @param array<int,array{name:string,role?:?string}> $partners
     */
    public function syncPartners(int $projectId, array $partners): void
    {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare('DELETE FROM project_partners WHERE project_id = :id');
            $del->execute(['id' => $projectId]);

            $ins = $this->db->prepare(
                'INSERT INTO project_partners (project_id, name, role, sort_order)
                 VALUES (:pid, :name, :role, :sort)'
            );
            $sort = 0;
            foreach ($partners as $p) {
                if (trim((string) ($p['name'] ?? '')) === '') {
                    continue;
                }
                $ins->execute([
                    'pid'  => $projectId,
                    'name' => $p['name'],
                    'role' => ($p['role'] ?? '') !== '' ? $p['role'] : null,
                    'sort' => $sort++,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Liste des projets avec montant de financement obtenu et nombre
     * d'opportunités rattachées (pour l'écran de liste).
     *
     * @return array<int,array<string,mixed>>
     */
    public function allWithFunding(): array
    {
        return $this->db->query(
            "SELECT p.*,
                    COALESCE(SUM(CASE WHEN f.status = 'obtenu' THEN f.amount ELSE 0 END), 0) AS funding_obtained,
                    COUNT(f.id) AS funding_count
             FROM {$this->table} p
             LEFT JOIN funding_opportunities f ON f.project_id = p.id
             GROUP BY p.id
             ORDER BY FIELD(p.status,'en_cours','planifie','en_pause','termine','annule'),
                      p.start_date DESC, p.created_at DESC"
        )->fetchAll();
    }

    /**
     * Liste simple (id => titre) pour les listes déroulantes.
     *
     * @return array<int,array<string,mixed>>
     */
    public function options(): array
    {
        return $this->db->query(
            "SELECT id, title FROM {$this->table} ORDER BY title ASC"
        )->fetchAll();
    }

    /**
     * Nombre total de bénéficiaires déclarés sur l'ensemble des projets.
     */
    public function sumBeneficiaries(): int
    {
        return (int) $this->db->query(
            "SELECT COALESCE(SUM(beneficiary_count), 0) FROM {$this->table}"
        )->fetchColumn();
    }

    /**
     * Répartition du nombre de projets par statut.
     *
     * @return array<string,int>
     */
    public function countByStatus(): array
    {
        $rows = $this->db->query(
            "SELECT status, COUNT(*) AS total FROM {$this->table} GROUP BY status"
        )->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['status']] = (int) $row['total'];
        }

        return $out;
    }
}
