<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Auth;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testIsLoggedInIsFalseWithoutSession(): void
    {
        $this->assertFalse(Auth::isLoggedIn());
    }

    public function testLoginPopulatesSessionAndIsLoggedIn(): void
    {
        Auth::login(['id' => 7, 'name' => 'Sec. Général', 'email' => 'sg@atlex-sport.com', 'role' => 'admin']);

        $this->assertTrue(Auth::isLoggedIn());
        $this->assertSame(7, Auth::id());
        $this->assertSame('admin', Auth::user()['role']);
        $this->assertSame('sg@atlex-sport.com', Auth::user()['email']);
    }

    public function testLoginDefaultsMissingNameAndRole(): void
    {
        Auth::login(['id' => 1]);

        $this->assertSame('Administrateur', Auth::user()['name']);
        $this->assertSame('editor', Auth::user()['role']);
    }

    public function testIsLoggedInRequiresBothIdAndRole(): void
    {
        $_SESSION['admin_id'] = 1;
        // Pas de rôle en session : ne doit pas être considéré comme connecté.
        $this->assertFalse(Auth::isLoggedIn());
    }
}
