<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

final class VisitTracker
{
    private const VISITOR_COOKIE = 'atlex_visitor';
    private const SESSION_COOKIE = 'atlex_session';
    private const SESSION_TTL = 1800;
    private const VISITOR_TTL = 31536000;

    private ?PDO $pdo = null;

    /**
     * @param array<string,mixed> $context
     */
    public function track(array $context = []): void
    {
        try {
            if ($this->isBotRequest()) {
                return;
            }

            $pdo = $this->pdo();

            $now = new \DateTimeImmutable('now');
            $visitedAt = $now->format('Y-m-d H:i:s');
            $visitDate = $now->format('Y-m-d');

            $visitorId = $this->getOrCreateVisitorId();
            $sessionId = $this->getOrCreateSessionId();

            $ip = $this->getClientIp();
            $userAgent = $this->server('HTTP_USER_AGENT');
            $acceptLanguage = $this->server('HTTP_ACCEPT_LANGUAGE');

            $uri = $this->server('REQUEST_URI', '/');
            $path = (string) parse_url($uri, PHP_URL_PATH);
            $query = (string) parse_url($uri, PHP_URL_QUERY);

            $scheme = $this->detectScheme();
            $host = $this->server('HTTP_HOST', '');
            $pageUrl = $host !== '' ? $scheme . '://' . $host . $uri : $uri;

            $referrerUrl = $this->server('HTTP_REFERER');
            $referrerHost = $this->extractHost($referrerUrl);
            $referrerSource = $this->detectReferrerSource($referrerHost);

            $utmSource = $this->queryParam('utm_source');
            $utmMedium = $this->queryParam('utm_medium');
            $utmCampaign = $this->queryParam('utm_campaign');
            $utmTerm = $this->queryParam('utm_term');
            $utmContent = $this->queryParam('utm_content');

            $deviceType = $this->detectDeviceType($userAgent);
            $browserName = $this->detectBrowser($userAgent);
            $osName = $this->detectOs($userAgent);

            $title = isset($context['title']) && is_string($context['title'])
                ? trim($context['title'])
                : null;

            $pagePath = $path !== '' ? $path : '/';
            $pageQuery = $query !== '' ? $query : null;

            $ipHash = $ip !== null ? hash('sha256', $ip) : null;
            $visitorKey = hash('sha256', $visitorId);
            $sessionKey = hash('sha256', $sessionId);

            $isUniqueDaily = $this->isUniqueDailyVisit($pdo, $visitDate, $visitorKey);
            $isEntrance = $this->isSessionFirstHit($pdo, $sessionKey);

            $stmt = $pdo->prepare(
                'INSERT INTO visits (
                    visitor_key,
                    session_key,
                    ip,
                    ip_hash,
                    user_agent,
                    language_code,
                    visit_date,
                    visited_at,
                    page_url,
                    page_path,
                    page_query,
                    page_title,
                    referrer_url,
                    referrer_host,
                    referrer_source,
                    utm_source,
                    utm_medium,
                    utm_campaign,
                    utm_term,
                    utm_content,
                    device_type,
                    os_name,
                    browser_name,
                    is_bot,
                    is_public,
                    is_unique_daily,
                    is_entrance,
                    created_at
                ) VALUES (
                    :visitor_key,
                    :session_key,
                    :ip,
                    :ip_hash,
                    :user_agent,
                    :language_code,
                    :visit_date,
                    :visited_at,
                    :page_url,
                    :page_path,
                    :page_query,
                    :page_title,
                    :referrer_url,
                    :referrer_host,
                    :referrer_source,
                    :utm_source,
                    :utm_medium,
                    :utm_campaign,
                    :utm_term,
                    :utm_content,
                    :device_type,
                    :os_name,
                    :browser_name,
                    :is_bot,
                    :is_public,
                    :is_unique_daily,
                    :is_entrance,
                    :created_at
                )'
            );

            $stmt->execute([
                'visitor_key' => $visitorKey,
                'session_key' => $sessionKey,
                'ip' => $ip,
                'ip_hash' => $ipHash,
                'user_agent' => $userAgent,
                'language_code' => $this->normalizeLanguage($acceptLanguage),
                'visit_date' => $visitDate,
                'visited_at' => $visitedAt,
                'page_url' => $pageUrl,
                'page_path' => $pagePath,
                'page_query' => $pageQuery,
                'page_title' => $title,
                'referrer_url' => $referrerUrl,
                'referrer_host' => $referrerHost,
                'referrer_source' => $referrerSource,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'utm_term' => $utmTerm,
                'utm_content' => $utmContent,
                'device_type' => $deviceType,
                'os_name' => $osName,
                'browser_name' => $browserName,
                'is_bot' => 0,
                'is_public' => 1,
                'is_unique_daily' => $isUniqueDaily ? 1 : 0,
                'is_entrance' => $isEntrance ? 1 : 0,
                'created_at' => $visitedAt,
            ]);

            $this->upsertSession(
                $pdo,
                $sessionKey,
                $visitorKey,
                $visitedAt,
                $visitDate,
                $pageUrl,
                $pagePath,
                $referrerUrl,
                $referrerHost,
                $referrerSource,
                $utmSource,
                $utmMedium,
                $utmCampaign,
                $utmTerm,
                $utmContent,
                $deviceType,
                $osName,
                $browserName
            );
        } catch (\Throwable $e) {
            error_log('[VisitTracker] ' . $e->getMessage());
        }
    }

    private function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $db   = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $this->pdo;
    }

    private function getOrCreateVisitorId(): string
    {
        $cookie = $_COOKIE[self::VISITOR_COOKIE] ?? null;
        if (is_string($cookie) && $cookie !== '') {
            return $cookie;
        }

        $visitorId = bin2hex(random_bytes(16));
        $this->setCookie(self::VISITOR_COOKIE, $visitorId, time() + self::VISITOR_TTL);

        return $visitorId;
    }

    private function getOrCreateSessionId(): string
    {
        $cookie = $_COOKIE[self::SESSION_COOKIE] ?? null;
        if (is_string($cookie) && $cookie !== '') {
            $this->setCookie(self::SESSION_COOKIE, $cookie, time() + self::SESSION_TTL);
            return $cookie;
        }

        $sessionId = bin2hex(random_bytes(16));
        $this->setCookie(self::SESSION_COOKIE, $sessionId, time() + self::SESSION_TTL);

        return $sessionId;
    }

    private function setCookie(string $name, string $value, int $expires): void
    {
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $_COOKIE[$name] = $value;
    }

    private function isUniqueDailyVisit(PDO $pdo, string $visitDate, string $visitorKey): bool
    {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM visits
             WHERE visit_date = :visit_date
               AND visitor_key = :visitor_key
             LIMIT 1'
        );

        $stmt->execute([
            'visit_date' => $visitDate,
            'visitor_key' => $visitorKey,
        ]);

        return $stmt->fetchColumn() === false;
    }

    private function isSessionFirstHit(PDO $pdo, string $sessionKey): bool
    {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM visits
             WHERE session_key = :session_key
             LIMIT 1'
        );

        $stmt->execute([
            'session_key' => $sessionKey,
        ]);

        return $stmt->fetchColumn() === false;
    }

    private function upsertSession(
        PDO $pdo,
        string $sessionKey,
        string $visitorKey,
        string $visitedAt,
        string $visitDate,
        string $pageUrl,
        string $pagePath,
        ?string $referrerUrl,
        ?string $referrerHost,
        ?string $referrerSource,
        ?string $utmSource,
        ?string $utmMedium,
        ?string $utmCampaign,
        ?string $utmTerm,
        ?string $utmContent,
        ?string $deviceType,
        ?string $osName,
        ?string $browserName
    ): void {
        $select = $pdo->prepare(
            'SELECT id, pageviews_count, entry_page_url, entry_page_path
             FROM visit_sessions
             WHERE session_key = :session_key
             LIMIT 1'
        );

        $select->execute([
            'session_key' => $sessionKey,
        ]);

        $session = $select->fetch();

        if ($session === false) {
            $insert = $pdo->prepare(
                'INSERT INTO visit_sessions (
                    session_key,
                    visitor_key,
                    started_at,
                    ended_at,
                    session_date,
                    entry_page_url,
                    entry_page_path,
                    exit_page_url,
                    exit_page_path,
                    pageviews_count,
                    unique_pageviews_count,
                    duration_seconds,
                    is_bounce,
                    referrer_url,
                    referrer_host,
                    referrer_source,
                    utm_source,
                    utm_medium,
                    utm_campaign,
                    utm_term,
                    utm_content,
                    device_type,
                    os_name,
                    browser_name,
                    is_bot,
                    is_public,
                    created_at,
                    updated_at
                ) VALUES (
                    :session_key,
                    :visitor_key,
                    :started_at,
                    :ended_at,
                    :session_date,
                    :entry_page_url,
                    :entry_page_path,
                    :exit_page_url,
                    :exit_page_path,
                    :pageviews_count,
                    :unique_pageviews_count,
                    :duration_seconds,
                    :is_bounce,
                    :referrer_url,
                    :referrer_host,
                    :referrer_source,
                    :utm_source,
                    :utm_medium,
                    :utm_campaign,
                    :utm_term,
                    :utm_content,
                    :device_type,
                    :os_name,
                    :browser_name,
                    :is_bot,
                    :is_public,
                    :created_at,
                    :updated_at
                )'
            );

            $insert->execute([
                'session_key' => $sessionKey,
                'visitor_key' => $visitorKey,
                'started_at' => $visitedAt,
                'ended_at' => $visitedAt,
                'session_date' => $visitDate,
                'entry_page_url' => $pageUrl,
                'entry_page_path' => $pagePath,
                'exit_page_url' => $pageUrl,
                'exit_page_path' => $pagePath,
                'pageviews_count' => 1,
                'unique_pageviews_count' => 1,
                'duration_seconds' => 0,
                'is_bounce' => 1,
                'referrer_url' => $referrerUrl,
                'referrer_host' => $referrerHost,
                'referrer_source' => $referrerSource,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'utm_term' => $utmTerm,
                'utm_content' => $utmContent,
                'device_type' => $deviceType,
                'os_name' => $osName,
                'browser_name' => $browserName,
                'is_bot' => 0,
                'is_public' => 1,
                'created_at' => $visitedAt,
                'updated_at' => $visitedAt,
            ]);

            return;
        }

        $update = $pdo->prepare(
            'UPDATE visit_sessions
             SET ended_at = :ended_at,
                 exit_page_url = :exit_page_url,
                 exit_page_path = :exit_page_path,
                 pageviews_count = pageviews_count + 1,
                 is_bounce = 0,
                 duration_seconds = GREATEST(TIMESTAMPDIFF(SECOND, started_at, :ended_at_duration), 0),
                 updated_at = :updated_at
             WHERE session_key = :session_key'
        );

        $update->execute([
            'ended_at' => $visitedAt,
            'exit_page_url' => $pageUrl,
            'exit_page_path' => $pagePath,
            'ended_at_duration' => $visitedAt,
            'updated_at' => $visitedAt,
            'session_key' => $sessionKey,
        ]);
    }

    private function isBotRequest(): bool
    {
        $ua = strtolower($this->server('HTTP_USER_AGENT', ''));

        if ($ua === '') {
            return true;
        }

        $bots = [
            'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget',
            'facebookexternalhit', 'preview', 'monitor', 'uptime',
            'python-requests', 'headless', 'scanner'
        ];

        foreach ($bots as $bot) {
            if (str_contains($ua, $bot)) {
                return true;
            }
        }

        return false;
    }

    private function getClientIp(): ?string
    {
        $candidates = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($candidates as $key) {
            $value = $this->server($key);
            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = explode(',', $value);
                $value = trim($parts[0] ?? '');
            }

            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }

        return null;
    }

    private function detectScheme(): string
    {
        return $this->isHttps() ? 'https' : 'http';
    }

    private function isHttps(): bool
    {
        $https = strtolower((string) $this->server('HTTPS', ''));
        $forwardedProto = strtolower((string) $this->server('HTTP_X_FORWARDED_PROTO', ''));
        $forwardedSsl = strtolower((string) $this->server('HTTP_X_FORWARDED_SSL', ''));

        return $https === 'on' || $https === '1' || $forwardedProto === 'https' || $forwardedSsl === 'on';
    }

    private function extractHost(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }

    private function detectReferrerSource(?string $host): string
    {
        if ($host === null || $host === '') {
            return 'direct';
        }

        $map = [
            'google.' => 'search',
            'bing.' => 'search',
            'yahoo.' => 'search',
            'duckduckgo.' => 'search',
            'facebook.' => 'social',
            'm.facebook.' => 'social',
            'instagram.' => 'social',
            'l.instagram.' => 'social',
            't.co' => 'social',
            'twitter.' => 'social',
            'x.com' => 'social',
            'linkedin.' => 'social',
            'youtube.' => 'social',
            'whatsapp.' => 'social',
        ];

        foreach ($map as $needle => $type) {
            if (str_contains($host, $needle)) {
                return $type;
            }
        }

        return 'referral';
    }

    private function detectDeviceType(?string $ua): string
    {
        $ua = strtolower((string) $ua);

        if ($ua === '') {
            return 'unknown';
        }

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (
            str_contains($ua, 'mobile') ||
            str_contains($ua, 'iphone') ||
            str_contains($ua, 'android')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(?string $ua): string
    {
        $ua = strtolower((string) $ua);

        return match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'chrome/') && !str_contains($ua, 'edg/') => 'Chrome',
            str_contains($ua, 'safari/') && !str_contains($ua, 'chrome/') => 'Safari',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'msie'), str_contains($ua, 'trident/') => 'Internet Explorer',
            default => 'Other',
        };
    }

    private function detectOs(?string $ua): string
    {
        $ua = strtolower((string) $ua);

        return match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Other',
        };
    }

    private function normalizeLanguage(?string $acceptLanguage): ?string
    {
        if ($acceptLanguage === null || trim($acceptLanguage) === '') {
            return null;
        }

        $parts = explode(',', $acceptLanguage);
        $primary = strtolower(trim($parts[0] ?? ''));

        return $primary !== '' ? substr($primary, 0, 10) : null;
    }

    private function queryParam(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? substr($value, 0, 150) : null;
    }

    private function server(string $key, ?string $default = null): ?string
    {
        $value = $_SERVER[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }
}