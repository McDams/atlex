<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VisitStat
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function dailySeries(int $days = 30): array
    {
        $sql = '
            SELECT
                visit_date,
                COUNT(*) AS pageviews,
                COUNT(DISTINCT visitor_key) AS unique_visitors,
                COUNT(DISTINCT session_key) AS sessions
            FROM visits
            WHERE is_public = 1
              AND is_bot = 0
              AND visit_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY visit_date
            ORDER BY visit_date ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string,mixed>
     */
    public function overview(int $days = 30): array
    {
        $from = (new \DateTimeImmutable())->modify('-' . max(1, $days - 1) . ' days')->format('Y-m-d');
        $to = (new \DateTimeImmutable())->format('Y-m-d');

        $visitModel = new Visit($this->pdo);
        $sessionModel = new VisitSession($this->pdo);

        return [
            'pageviews' => $visitModel->totalPageviews($from, $to),
            'visitors' => $visitModel->uniqueVisitors($from, $to),
            'sessions' => $sessionModel->totalSessions($from, $to),
            'bounce_rate' => $sessionModel->bounceRate($from, $to),
            'avg_duration' => $sessionModel->averageDuration($from, $to),
        ];
    }
}