<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VisitSession
{
    public function __construct(private PDO $pdo)
    {
    }

    public function totalSessions(?string $from = null, ?string $to = null): int
    {
        $sql = 'SELECT COUNT(*) FROM visit_sessions WHERE is_public = 1 AND is_bot = 0';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND session_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND session_date <= :to';
            $params['to'] = $to;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function bounceSessions(?string $from = null, ?string $to = null): int
    {
        $sql = 'SELECT COUNT(*) FROM visit_sessions WHERE is_public = 1 AND is_bot = 0 AND is_bounce = 1';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND session_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND session_date <= :to';
            $params['to'] = $to;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function averageDuration(?string $from = null, ?string $to = null): int
    {
        $sql = 'SELECT AVG(duration_seconds) FROM visit_sessions WHERE is_public = 1 AND is_bot = 0';
        $params = [];

        if ($from !== null) {
            $sql .= ' AND session_date >= :from';
            $params['from'] = $from;
        }

        if ($to !== null) {
            $sql .= ' AND session_date <= :to';
            $params['to'] = $to;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) round((float) $stmt->fetchColumn());
    }

    public function bounceRate(?string $from = null, ?string $to = null): float
    {
        $sessions = $this->totalSessions($from, $to);
        if ($sessions === 0) {
            return 0.0;
        }

        $bounces = $this->bounceSessions($from, $to);

        return round(($bounces / $sessions) * 100, 2);
    }
}