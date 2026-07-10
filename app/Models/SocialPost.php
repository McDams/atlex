<?php

declare(strict_types=1);

namespace App\Models;

/**
 * File de brouillons de posts réseaux sociaux (générés par l'IA ou créés
 * manuellement). Rien n'est publié depuis cette table sans passage explicite
 * par le statut « approuve » via l'admin.
 */
final class SocialPost extends BaseModel
{
    protected string $table = 'social_posts';

    protected array $fillable = [
        'platform', 'status', 'content_text', 'source_type', 'source_id',
        'media_path', 'scheduled_at', 'published_at', 'external_post_id',
        'error_message', 'created_by',
    ];

    /**
     * Liste filtrable par statut et/ou plateforme.
     *
     * @return array<int,array<string,mixed>>
     */
    public function filtered(string $status = 'brouillon', ?string $platform = null): array
    {
        $where = [];
        $params = [];

        if ($status !== '' && $status !== 'tous') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($platform !== null && $platform !== '') {
            $where[] = 'platform = :platform';
            $params['platform'] = $platform;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY COALESCE(scheduled_at, created_at) DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int
    {
        return $this->count(['status' => $status]);
    }

    /**
     * Empêche de reproposer deux fois le même article/événement/résultat
     * source sur la même plateforme.
     */
    public function alreadyProposed(string $sourceType, int $sourceId, string $platform): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table}
             WHERE source_type = :source_type AND source_id = :source_id AND platform = :platform
             LIMIT 1"
        );
        $stmt->execute([
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'platform'    => $platform,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function markPublished(int $id, string $externalPostId): bool
    {
        return $this->update($id, [
            'status'           => 'publie',
            'external_post_id' => $externalPostId,
            'published_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(int $id, string $errorMessage): bool
    {
        return $this->update($id, [
            'status'        => 'echec',
            'error_message' => mb_substr($errorMessage, 0, 500),
        ]);
    }
}
