<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/atlex_ratelimit_test_' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            foreach (glob($this->dir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->dir);
        }
    }

    public function testHitIncrementsAttemptCount(): void
    {
        $limiter = new RateLimiter($this->dir);

        $this->assertSame(1, $limiter->hit('login:1.2.3.4', 60));
        $this->assertSame(2, $limiter->hit('login:1.2.3.4', 60));
        $this->assertSame(2, $limiter->attempts('login:1.2.3.4'));
    }

    public function testTooManyAttemptsRespectsThreshold(): void
    {
        $limiter = new RateLimiter($this->dir);

        for ($i = 0; $i < 5; $i++) {
            $limiter->hit('login:5.6.7.8', 900);
        }

        $this->assertTrue($limiter->tooManyAttempts('login:5.6.7.8', 5));
        $this->assertFalse($limiter->tooManyAttempts('login:5.6.7.8', 6));
    }

    public function testClearResetsCounter(): void
    {
        $limiter = new RateLimiter($this->dir);
        $limiter->hit('login:9.9.9.9', 60);

        $limiter->clear('login:9.9.9.9');

        $this->assertSame(0, $limiter->attempts('login:9.9.9.9'));
    }

    public function testDistinctKeysAreIndependent(): void
    {
        $limiter = new RateLimiter($this->dir);
        $limiter->hit('login:a', 60);
        $limiter->hit('login:a', 60);
        $limiter->hit('login:b', 60);

        $this->assertSame(2, $limiter->attempts('login:a'));
        $this->assertSame(1, $limiter->attempts('login:b'));
    }
}
