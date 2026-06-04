<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Communiqués de presse (Centre média).
 */
final class PressRelease extends BaseModel
{
    protected string $table = 'press_releases';

    protected array $fillable = [
        'title', 'slug', 'reference', 'excerpt', 'content', 'file', 'is_published', 'published_at',
    ];

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY published_at DESC, created_at DESC"
        )->fetchAll();
    }

    /**
     * Communiqués publiés (page publique).
     *
     * @return array<int,array<string,mixed>>
     */
    public function published(int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_published = 1
                ORDER BY published_at DESC, created_at DESC";
        if ($limit > 0) {
            $sql .= ' LIMIT :limit';
        }

        $stmt = $this->db->prepare($sql);
        if ($limit > 0) {
            $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function publishedBySlug(string $slug): ?array
    {
        $row = $this->findBy('slug', $slug);

        return ($row !== null && (int) $row['is_published'] === 1) ? $row : null;
    }

    public function togglePublished(int $id): void
    {
        $row = $this->find($id);
        if ($row === null) {
            return;
        }
        $newState = (int) $row['is_published'] === 1 ? 0 : 1;
        $data = ['is_published' => $newState];
        if ($newState === 1 && empty($row['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        $this->update($id, $data);
    }

    public function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}
