<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Dons en ligne (MTN MoMo + PayPal).
 */
final class Donation extends BaseModel
{
    protected string $table = 'donations';

    protected array $fillable = [
        'reference', 'method', 'amount', 'currency', 'donor_name', 'donor_email',
        'donor_phone', 'status', 'external_reference', 'provider_payload',
    ];

    /**
     * @return array<string,mixed>|null
     */
    public function findByReference(string $reference): ?array
    {
        return $this->findBy('reference', $reference);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByExternalReference(string $externalReference): ?array
    {
        return $this->findBy('external_reference', $externalReference);
    }

    /**
     * Liste filtrable par statut et/ou méthode, plus récentes d'abord.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtered(string $status = 'tous', ?string $method = null): array
    {
        $where = [];
        $params = [];

        if ($status !== '' && $status !== 'tous') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($method !== null && $method !== '') {
            $where[] = 'method = :method';
            $params['method'] = $method;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Somme des dons complétés (pour affichage tableau de bord), par devise.
     *
     * @return array<int,array{currency:string,total:string,count:int}>
     */
    public function totalsByCurrency(): array
    {
        $stmt = $this->db->query(
            "SELECT currency, SUM(amount) AS total, COUNT(*) AS count
             FROM {$this->table}
             WHERE status = 'completed'
             GROUP BY currency"
        );

        return $stmt->fetchAll();
    }

    /**
     * Met à jour le statut d'un don, avec la réponse fournisseur associée.
     * Idempotent : n'écrase pas un don déjà 'completed'.
     */
    public function updateStatus(int $id, string $status, ?string $externalReference = null, ?string $providerPayload = null): bool
    {
        $current = $this->find($id);
        if ($current === null || $current['status'] === 'completed') {
            return false;
        }

        $data = ['status' => $status];
        if ($externalReference !== null) {
            $data['external_reference'] = $externalReference;
        }
        if ($providerPayload !== null) {
            $data['provider_payload'] = mb_substr($providerPayload, 0, 60000);
        }

        return $this->update($id, $data);
    }
}
