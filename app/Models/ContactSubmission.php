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
    ];

    public function countUnread(): int
    {
        return $this->count(['is_read' => 0]);
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
