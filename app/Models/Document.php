<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Documents internes du Secrétariat Général.
 */
final class Document extends BaseModel
{
    protected string $table = 'documents';

    protected array $fillable = [
        'title', 'filename', 'file_type', 'file_size', 'category', 'uploaded_by',
    ];

    /**
     * Documents avec le nom de l'auteur du téléversement.
     *
     * @return array<int,array<string,mixed>>
     */
    public function allWithUploader(): array
    {
        return $this->db->query(
            "SELECT d.*, u.name AS uploader_name
             FROM {$this->table} d
             LEFT JOIN users u ON u.id = d.uploaded_by
             ORDER BY d.created_at DESC"
        )->fetchAll();
    }
}
