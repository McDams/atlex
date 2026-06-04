<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Opportunités de financement détectées automatiquement (file de veille).
 */
final class FundingLead extends BaseModel
{
    protected string $table = 'funding_leads';

    protected array $fillable = [
        'source_id', 'title', 'url', 'url_hash', 'summary', 'source_name', 'published_at', 'status',
    ];

    /**
     * Insère une opportunité détectée si son URL n'est pas déjà connue.
     * Retourne true si une nouvelle ligne a été créée.
     *
     * @param array<string,mixed> $data
     */
    public function insertIfNew(array $data): bool
    {
        $data['url_hash'] = sha1((string) $data['url']);

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO ' . $this->table . '
                (source_id, title, url, url_hash, summary, source_name, published_at, status)
             VALUES (:source_id, :title, :url, :url_hash, :summary, :source_name, :published_at, :status)'
        );
        $stmt->execute([
            'source_id'    => $data['source_id'] ?? null,
            'title'        => mb_substr((string) $data['title'], 0, 400),
            'url'          => mb_substr((string) $data['url'], 0, 700),
            'url_hash'     => $data['url_hash'],
            'summary'      => $data['summary'] ?? null,
            'source_name'  => $data['source_name'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'status'       => 'nouveau',
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Liste filtrable, avec le nom de la source.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtered(string $status = 'nouveau', ?int $sourceId = null): array
    {
        $where = [];
        $params = [];

        if ($status !== '' && $status !== 'tous') {
            $where[] = 'l.status = :status';
            $params['status'] = $status;
        }
        if ($sourceId !== null) {
            $where[] = 'l.source_id = :sid';
            $params['sid'] = $sourceId;
        }

        $sql = "SELECT l.*, s.name AS source_label
                FROM {$this->table} l
                LEFT JOIN funding_sources s ON s.id = l.source_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY l.published_at IS NULL, l.published_at DESC, l.fetched_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countNew(): int
    {
        return $this->count(['status' => 'nouveau']);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :s WHERE id = :id");
        $stmt->execute(['s' => $status, 'id' => $id]);
    }
}
