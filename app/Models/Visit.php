<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Visit
{
    public function __construct(private PDO $pdo)
    {
    }

    public function totalPageviews(?string $from = null, ?string $to = null): int
    {
        $sql = 'SELECT COUNT(*) FROM visits WHERE is_public = 1 AND is_bot = 0';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function uniqueVisitors(?string $from = null, ?string $to = null): int
    {
        $sql = 'SELECT COUNT(DISTINCT visitor_key) FROM visits WHERE is_public = 1 AND is_bot = 0';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function topPages(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        $sql = '
            SELECT
                page_path,
                COALESCE(page_title, page_path) AS page_title,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors
            FROM visits
            WHERE is_public = 1 AND is_bot = 0
        ';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $sql .= '
            GROUP BY page_path, page_title
            ORDER BY pageviews DESC
            LIMIT ' . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function topCountries(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        $sql = '
            SELECT
                COALESCE(country_name, "Inconnu") AS country_name,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors
            FROM visits
            WHERE is_public = 1 AND is_bot = 0
        ';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $sql .= '
            GROUP BY country_name
            ORDER BY pageviews DESC
            LIMIT ' . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function topSources(int $limit = 10, ?string $from = null, ?string $to = null): array
    {
        $sql = '
            SELECT
                COALESCE(referrer_source, "direct") AS source_name,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors
            FROM visits
            WHERE is_public = 1 AND is_bot = 0
        ';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $sql .= '
            GROUP BY source_name
            ORDER BY pageviews DESC
            LIMIT ' . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function deviceBreakdown(?string $from = null, ?string $to = null): array
    {
        $sql = '
            SELECT
                COALESCE(device_type, "unknown") AS device_type,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors
            FROM visits
            WHERE is_public = 1 AND is_bot = 0
        ';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $sql .= '
            GROUP BY device_type
            ORDER BY pageviews DESC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function browserBreakdown(?string $from = null, ?string $to = null): array
    {
        $sql = '
            SELECT
                COALESCE(browser_name, "Other") AS browser_name,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors
            FROM visits
            WHERE is_public = 1 AND is_bot = 0
        ';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND visit_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND visit_date <= :to';
            $params['to'] = $to;
        }

        $sql .= '
            GROUP BY browser_name
            ORDER BY pageviews DESC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}