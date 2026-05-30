<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modèle de base : opérations CRUD génériques via PDO prepared statements.
 */
abstract class BaseModel
{
    protected PDO $db;

    /** Nom de la table SQL (défini par les sous-classes). */
    protected string $table = '';

    /** Clé primaire. */
    protected string $primaryKey = 'id';

    /** @var array<int,string> Colonnes autorisées en écriture. */
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère un enregistrement par sa clé primaire.
     *
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Récupère tous les enregistrements.
     *
     * @return array<int,array<string,mixed>>
     */
    public function findAll(string $orderBy = null, string $direction = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy !== null) {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Récupère le premier enregistrement correspondant à une colonne.
     *
     * @return array<string,mixed>|null
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1"
        );
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Insère un enregistrement et retourne son identifiant.
     *
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $columns = array_keys($data);

        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un enregistrement par sa clé primaire.
     *
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if ($data === []) {
            return false;
        }

        $assignments = array_map(static fn (string $c): string => "$c = :$c", array_keys($data));
        $data['_pk'] = $id;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :_pk',
            $this->table,
            implode(', ', $assignments),
            $this->primaryKey
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Supprime un enregistrement par sa clé primaire.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id"
        );
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Compte les enregistrements (avec clause WHERE optionnelle).
     *
     * @param array<string,mixed> $conditions
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) AS aggregate FROM {$this->table}";
        $params = [];

        if ($conditions !== []) {
            $clauses = [];
            foreach ($conditions as $column => $value) {
                $clauses[] = "$column = :$column";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Ne conserve que les colonnes autorisées en écriture.
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    protected function filterFillable(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}
