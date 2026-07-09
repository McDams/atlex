<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

final class CountryFlagTest extends TestCase
{
    public function testReturnsFlagEmojiForValidIsoCode(): void
    {
        $this->assertSame('🇧🇯', country_flag('bj'));
        $this->assertSame('🇫🇷', country_flag('FR'));
    }

    public function testReturnsWhiteFlagForInvalidCode(): void
    {
        $this->assertSame('🏳️', country_flag(null));
        $this->assertSame('🏳️', country_flag(''));
        $this->assertSame('🏳️', country_flag('XYZ'));
        $this->assertSame('🏳️', country_flag('1'));
    }
}
