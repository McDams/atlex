<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Candidatures bénévoles soumises depuis la page Contact.
 */
final class VolunteerRequest extends BaseModel
{
    protected string $table = 'volunteer_requests';

    protected array $fillable = [
        'first_name', 'last_name', 'phone', 'email',
        'missions', 'message', 'status',
    ];

    /** @var array<string,string> Statuts possibles et leur libellé. */
    public const STATUSES = [
        'nouveau'  => 'Nouveau',
        'en_cours' => 'En cours',
        'accepte'  => 'Accepté',
        'refuse'   => 'Refusé',
    ];

    /**
     * Récupère toutes les candidatures, plus récentes en premier.
     *
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return $this->findAll('created_at', 'DESC');
    }

    /**
     * Met à jour le statut d'une candidature.
     */
    public function updateStatus(int $id, string $status): bool
    {
        if (!isset(self::STATUSES[$status])) {
            return false;
        }

        return $this->update($id, ['status' => $status]);
    }

    /**
     * Compte les candidatures regroupées par statut.
     *
     * @return array<string,int> ex: ['total' => 5, 'nouveau' => 2, ...]
     */
    public function countByStatus(): array
    {
        $counts = ['total' => 0];
        foreach (array_keys(self::STATUSES) as $status) {
            $counts[$status] = 0;
        }

        $rows = $this->db
            ->query("SELECT status, COUNT(*) AS total FROM {$this->table} GROUP BY status")
            ->fetchAll();

        foreach ($rows as $row) {
            $status = (string) $row['status'];
            $n = (int) $row['total'];
            $counts[$status] = $n;
            $counts['total'] += $n;
        }

        return $counts;
    }
}
