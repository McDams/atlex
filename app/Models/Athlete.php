<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Athlètes — profils numériques publics (photo, palmarès, classement, vidéos,
 * résultats). Distincts des membres (gestion administrative interne).
 *
 * Les collections liées (palmarès, résultats, vidéos) sont stockées dans des
 * tables enfants avec suppression en cascade ; elles sont remplacées en bloc
 * à chaque enregistrement via syncRelations().
 */
final class Athlete extends BaseModel
{
    protected string $table = 'athletes';

    protected array $fillable = [
        'slug', 'first_name', 'last_name', 'discipline',
        'category', 'ranking', 'photo', 'bio', 'is_published', 'sort_order',
    ];

    /**
     * Liste ordonnée (espace SG).
     *
     * @return array<int,array<string,mixed>>
     */
    public function allOrdered(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY sort_order ASC, last_name ASC, first_name ASC"
        )->fetchAll();
    }

    /**
     * Athlètes publiés (site public), filtrables par discipline.
     *
     * @return array<int,array<string,mixed>>
     */
    public function published(?string $discipline = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_published = 1";
        $params = [];

        if ($discipline !== null && $discipline !== '') {
            $sql .= ' AND discipline = :d';
            $params['d'] = $discipline;
        }

        $sql .= ' ORDER BY sort_order ASC, last_name ASC, first_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Profil public complet par slug (athlète + collections liées).
     *
     * @return array<string,mixed>|null
     */
    public function profileBySlug(string $slug): ?array
    {
        $athlete = $this->findBy('slug', $slug);
        if ($athlete === null || (int) $athlete['is_published'] !== 1) {
            return null;
        }

        return $this->withRelations($athlete);
    }

    /**
     * Athlète + collections liées par identifiant (espace SG, publié ou non).
     *
     * @return array<string,mixed>|null
     */
    public function findWithRelations(int $id): ?array
    {
        $athlete = $this->find($id);

        return $athlete === null ? null : $this->withRelations($athlete);
    }

    /**
     * Greffe les collections liées sur un enregistrement athlète.
     *
     * @param array<string,mixed> $athlete
     * @return array<string,mixed>
     */
    private function withRelations(array $athlete): array
    {
        $id = (int) $athlete['id'];
        $athlete['achievements'] = $this->relation('athlete_achievements', $id);
        $athlete['results']      = $this->relation('athlete_results', $id);
        $athlete['videos']       = $this->relation('athlete_videos', $id);

        return $athlete;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function relation(string $table, int $athleteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$table} WHERE athlete_id = :id ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute(['id' => $athleteId]);

        return $stmt->fetchAll();
    }

    /**
     * Remplace en bloc les collections liées d'un athlète.
     *
     * @param array<int,array{year?:?string,title:string,position?:?string}> $achievements
     * @param array<int,array{result_date?:?string,competition:string,result?:?string}> $results
     * @param array<int,array{title?:?string,url:string}> $videos
     */
    public function syncRelations(int $athleteId, array $achievements, array $results, array $videos): void
    {
        $this->db->beginTransaction();
        try {
            foreach (['athlete_achievements', 'athlete_results', 'athlete_videos'] as $t) {
                $stmt = $this->db->prepare("DELETE FROM {$t} WHERE athlete_id = :id");
                $stmt->execute(['id' => $athleteId]);
            }

            $sort = 0;
            $insAch = $this->db->prepare(
                'INSERT INTO athlete_achievements (athlete_id, year, title, position, sort_order)
                 VALUES (:aid, :year, :title, :position, :sort)'
            );
            foreach ($achievements as $a) {
                if (trim((string) ($a['title'] ?? '')) === '') {
                    continue;
                }
                $insAch->execute([
                    'aid'      => $athleteId,
                    'year'     => $a['year'] ?? null,
                    'title'    => $a['title'],
                    'position' => $a['position'] ?? null,
                    'sort'     => $sort++,
                ]);
            }

            $sort = 0;
            $insRes = $this->db->prepare(
                'INSERT INTO athlete_results (athlete_id, result_date, competition, result, sort_order)
                 VALUES (:aid, :rdate, :competition, :result, :sort)'
            );
            foreach ($results as $r) {
                if (trim((string) ($r['competition'] ?? '')) === '') {
                    continue;
                }
                $insRes->execute([
                    'aid'         => $athleteId,
                    'rdate'       => ($r['result_date'] ?? '') !== '' ? $r['result_date'] : null,
                    'competition' => $r['competition'],
                    'result'      => $r['result'] ?? null,
                    'sort'        => $sort++,
                ]);
            }

            $sort = 0;
            $insVid = $this->db->prepare(
                'INSERT INTO athlete_videos (athlete_id, title, url, sort_order)
                 VALUES (:aid, :title, :url, :sort)'
            );
            foreach ($videos as $v) {
                if (trim((string) ($v['url'] ?? '')) === '') {
                    continue;
                }
                $insVid->execute([
                    'aid'   => $athleteId,
                    'title' => $v['title'] ?? null,
                    'url'   => $v['url'],
                    'sort'  => $sort++,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Génère un slug unique à partir du nom (exclut éventuellement un id en édition).
     */
    public function uniqueSlug(string $firstName, string $lastName, ?int $excludeId = null): string
    {
        $base = slugify($firstName . '-' . $lastName);
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

    /**
     * Bascule l'état de publication.
     */
    public function togglePublished(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_published = NOT is_published WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * Nombre d'athlètes publiés par discipline (pour les filtres publics).
     *
     * @return array<string,int>
     */
    public function publishedCountByDiscipline(): array
    {
        $rows = $this->db->query(
            "SELECT discipline, COUNT(*) AS total FROM {$this->table}
             WHERE is_published = 1 GROUP BY discipline"
        )->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['discipline']] = (int) $row['total'];
        }

        return $out;
    }
}
