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

    // -------------------------------------------------------------------------
    // Démarches à suivre (checklist)
    // -------------------------------------------------------------------------

    /**
     * @return array<int,array<string,mixed>>
     */
    public function checklist(int $opportunityId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM funding_checklist WHERE opportunity_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $opportunityId]);

        return $stmt->fetchAll();
    }

    public function addChecklistItem(int $opportunityId, string $label): void
    {
        if (trim($label) === '') {
            return;
        }

        $stmt = $this->db->prepare(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM funding_checklist WHERE opportunity_id = :id'
        );
        $stmt->execute(['id' => $opportunityId]);
        $next = (int) $stmt->fetchColumn();

        $ins = $this->db->prepare(
            'INSERT INTO funding_checklist (opportunity_id, label, sort_order)
             VALUES (:id, :label, :sort)'
        );
        $ins->execute(['id' => $opportunityId, 'label' => mb_substr($label, 0, 300), 'sort' => $next]);
    }

    /**
     * Crée la checklist à partir d'une liste d'étapes (ignore si déjà présente).
     *
     * @param array<int,string> $labels
     */
    public function seedChecklist(int $opportunityId, array $labels): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM funding_checklist WHERE opportunity_id = :id');
        $stmt->execute(['id' => $opportunityId]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $ins = $this->db->prepare(
            'INSERT INTO funding_checklist (opportunity_id, label, sort_order)
             VALUES (:id, :label, :sort)'
        );
        $sort = 0;
        foreach ($labels as $label) {
            $label = trim($label);
            if ($label === '') {
                continue;
            }
            $ins->execute(['id' => $opportunityId, 'label' => mb_substr($label, 0, 300), 'sort' => $sort++]);
        }
    }

    public function toggleChecklistItem(int $itemId): void
    {
        $stmt = $this->db->prepare('UPDATE funding_checklist SET is_done = NOT is_done WHERE id = :id');
        $stmt->execute(['id' => $itemId]);
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $stmt = $this->db->prepare('DELETE FROM funding_checklist WHERE id = :id');
        $stmt->execute(['id' => $itemId]);
    }

    /**
     * Identifiant d'opportunité d'un item (pour la redirection après action).
     */
    public function checklistItemOpportunity(int $itemId): ?int
    {
        $stmt = $this->db->prepare('SELECT opportunity_id FROM funding_checklist WHERE id = :id');
        $stmt->execute(['id' => $itemId]);
        $val = $stmt->fetchColumn();

        return $val === false ? null : (int) $val;
    }
}
