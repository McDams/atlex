<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\CSRF;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CSRFTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    public function testGenerateTokenReturnsSameTokenOnRepeatedCalls(): void
    {
        $first = CSRF::generateToken();
        $second = CSRF::generateToken();

        $this->assertSame($first, $second);
        $this->assertSame(64, strlen($first)); // 32 octets -> 64 caractères hex
    }

    public function testValidateTokenAcceptsMatchingToken(): void
    {
        $token = CSRF::generateToken();

        $this->assertTrue(CSRF::validateToken($token));
    }

    public function testValidateTokenRejectsWrongToken(): void
    {
        CSRF::generateToken();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(419);
        CSRF::validateToken('un-jeton-invalide');
    }

    public function testValidateTokenRejectsNullToken(): void
    {
        CSRF::generateToken();

        $this->expectException(RuntimeException::class);
        CSRF::validateToken(null);
    }

    public function testCheckReadsTokenFromPostSuperglobal(): void
    {
        $token = CSRF::generateToken();
        $_POST['_token'] = $token;

        $this->assertTrue(CSRF::check());
    }

    public function testCheckFailsWithoutStoredToken(): void
    {
        $_POST['_token'] = 'peu-importe';

        $this->expectException(RuntimeException::class);
        CSRF::check();
    }
}
