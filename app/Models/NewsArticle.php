<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Articles d'actualités.
 */
final class NewsArticle extends BaseModel
{
    protected string $table = 'news_articles';

    protected array $fillable = [
        'title', 'slug', 'excerpt', 'content', 'category',
        'cover_image', 'is_published', 'published_at', 'author_id',
    ];

    /**
     * Derniers articles publiés.
     *
     * @return array<int,array<string,mixed>>
     */
    public function latest(int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE is_published = 1
             ORDER BY published_at DESC, created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Articles publiés paginés, avec filtre catégorie optionnel.
     *
     * @return array<int,array<string,mixed>>
     */
    public function paginate(int $page = 1, int $perPage = 12, ?string $category = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $where = 'is_published = 1';
        $params = [];
        if ($category !== null && $category !== '') {
            $where .= ' AND category = :category';
            $params['category'] = $category;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE {$where}
                ORDER BY published_at DESC, created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countPublished(?string $category = null): int
    {
        $where = 'is_published = 1';
        $params = [];
        if ($category !== null && $category !== '') {
            $where .= ' AND category = :category';
            $params['category'] = $category;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->findAll('created_at', 'DESC');
    }

    /**
     * Bascule l'état de publication.
     */
    public function togglePublished(int $id): bool
    {
        $article = $this->find($id);
        if ($article === null) {
            return false;
        }

        $newState = $article['is_published'] ? 0 : 1;
        $data = ['is_published' => $newState];
        if ($newState === 1 && empty($article['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $data);
    }
}
