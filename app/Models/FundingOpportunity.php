<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Opportunités de financement (tracker interne SG) : subventions, appels à
 * projets, sponsoring… Chaque opportunité peut être rattachée à un projet.
 */
final class FundingOpportunity extends BaseModel
{
    protected string $table = 'funding_opportunities';

    protected array $fillable = [
        'project_id', 'name', 'funder', 'type', 'amount',
        'deadline', 'status', 'application_url', 'notes', 'created_by',
    ];

    /**
     * Liste filtrable, avec le titre du projet rattaché.
     *
     * @return array<int,array<string,mixed>>
     */
    public function allFiltered(?string $status = null, ?int $projectId = null): array
    {
        $where = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'f.status = :status';
            $params['status'] = $status;
        }
        if ($projectId !== null) {
            $where[] = 'f.project_id = :pid';
            $params['pid'] = $projectId;
        }

        $sql = "SELECT f.*, p.title AS project_title
                FROM {$this->table} f
                LEFT JOIN projects p ON p.id = f.project_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY FIELD(f.status,'depose','en_preparation','identifie','obtenu','refuse'),
                          f.deadline IS NULL, f.deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Opportunités rattachées à un projet donné.
     *
     * @return array<int,array<string,mixed>>
     */
    public function byProject(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE project_id = :pid
             ORDER BY FIELD(status,'obtenu','depose','en_preparation','identifie','refuse'), deadline ASC"
        );
        $stmt->execute(['pid' => $projectId]);

        return $stmt->fetchAll();
    }

    /**
     * Indicateurs globaux du tracker.
     *
     * @return array{count:int,obtained:float,pipeline:float,by_status:array<string,int>}
     */
    public function dashboard(): array
    {
        $count = (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

        $obtained = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount),0) FROM {$this->table} WHERE status = 'obtenu'"
        )->fetchColumn();

        // Pipeline = montants en cours d'obtention (hors obtenu / refusé).
        $pipeline = (float) $this->db->query(
            "SELECT COALESCE(SUM(amount),0) FROM {$this->table}
             WHERE status IN ('identifie','en_preparation','depose')"
        )->fetchColumn();

        $rows = $this->db->query(
            "SELECT status, COUNT(*) AS total FROM {$this->table} GROUP BY status"
        )->fetchAll();
        $byStatus = [];
        foreach ($rows as $row) {
            $byStatus[(string) $row['status']] = (int) $row['total'];
        }

        return [
            'count'     => $count,
            'obtained'  => $obtained,
            'pipeline'  => $pipeline,
            'by_status' => $byStatus,
        ];
    }
}
