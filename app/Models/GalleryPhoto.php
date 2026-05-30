<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Photos de la galerie.
 */
final class GalleryPhoto extends BaseModel
{
    protected string $table = 'gallery_photos';

    protected array $fillable = [
        'title', 'filename', 'category', 'alt_text', 'is_published', 'sort_order',
    ];

    /**
     * Photos publiées, filtre catégorie optionnel.
     *
     * @return array<int,array<string,mixed>>
     */
    public function published(?string $category = null, int $limit = 0): array
    {
        $where = 'is_published = 1';
        $params = [];
        if ($category !== null && $category !== '' && $category !== 'all') {
            $where .= ' AND category = :category';
            $params['category'] = $category;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE {$where}
                ORDER BY sort_order ASC, created_at DESC";

        if ($limit > 0) {
            $sql .= ' LIMIT :limit';
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if ($limit > 0) {
            $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
