<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Soumissions des formulaires (contact, inscription, partenariat).
 */
final class ContactSubmission extends BaseModel
{
    protected string $table = 'contact_submissions';

    protected array $fillable = [
        'type', 'first_name', 'last_name', 'email', 'phone',
        'age', 'gender', 'discipline', 'message', 'is_read',
        'status', 'processed_at',
    ];

    public function countUnread(): int
    {
        return $this->count(['is_read' => 0]);
    }

    /**
     * Liste les demandes d'inscription, éventuellement filtrées par statut.
     *
     * @return array<int,array<string,mixed>>
     */
    public function inscriptions(?string $status = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = 'inscription'";
        $params = [];

        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Nombre de demandes d'inscription en attente de traitement.
     */
    public function countPendingInscriptions(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE type = 'inscription' AND status = 'nouveau'"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
