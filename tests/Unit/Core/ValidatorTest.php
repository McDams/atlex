<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredFailsOnEmptyValue(): void
    {
        $v = new Validator(['name' => '']);
        $v->validate(['name' => 'required']);

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors());
    }

    public function testRequiredPassesOnPresentValue(): void
    {
        $v = new Validator(['name' => 'ATLEX']);
        $v->validate(['name' => 'required']);

        $this->assertTrue($v->passes());
    }

    public function testEmailRuleRejectsInvalidAddress(): void
    {
        $v = new Validator(['email' => 'pas-un-email']);
        $v->validate(['email' => 'required|email']);

        $this->assertTrue($v->fails());
    }

    public function testEmailRuleAcceptsValidAddress(): void
    {
        $v = new Validator(['email' => 'contact@atlex-sport.com']);
        $v->validate(['email' => 'required|email']);

        $this->assertTrue($v->passes());
    }

    public function testMinAndMaxLengthBoundaries(): void
    {
        $v = new Validator(['bio' => 'ab']);
        $v->validate(['bio' => 'min:3|max:5']);
        $this->assertTrue($v->fails());

        $v = new Validator(['bio' => 'abcdef']);
        $v->validate(['bio' => 'min:3|max:5']);
        $this->assertTrue($v->fails());

        $v = new Validator(['bio' => 'abcd']);
        $v->validate(['bio' => 'min:3|max:5']);
        $this->assertTrue($v->passes());
    }

    public function testInRuleRestrictsToAllowedValues(): void
    {
        $v = new Validator(['category' => 'inconnu']);
        $v->validate(['category' => 'in:resultat,recrutement,evenement']);

        $this->assertTrue($v->fails());
    }

    public function testCustomMessageOverridesDefault(): void
    {
        $v = new Validator(['title' => '']);
        $v->validate(['title' => 'required'], ['title.required' => 'Le titre est requis.']);

        $this->assertSame(['Le titre est requis.'], $v->errors()['title']);
    }

    public function testOptionalFieldsSkipOtherRulesWhenEmpty(): void
    {
        $v = new Validator(['phone' => '']);
        $v->validate(['phone' => 'min:8']);

        $this->assertTrue($v->passes());
    }
}
