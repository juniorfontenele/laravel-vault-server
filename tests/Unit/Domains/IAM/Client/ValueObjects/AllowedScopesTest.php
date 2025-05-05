<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\InvalidScopeException;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class AllowedScopesTest extends TestCase
{
    public function testCreateAllowedScopes(): void
    {
        $scopes = new AllowedScopes([Scope::KEYS_READ, Scope::HASHES_READ]);

        $this->assertCount(2, $scopes->all());
        $this->assertEquals([Scope::KEYS_READ, Scope::HASHES_READ], $scopes->all());
    }

    public function testCreateAllowedScopesWithInvalidTypeThrowsException(): void
    {
        $this->expectException(InvalidScopeException::class);

        new AllowedScopes(['invalid-scope']);
    }

    public function testHasScope(): void
    {
        $scopes = new AllowedScopes([Scope::KEYS_READ, Scope::HASHES_READ]);

        $this->assertTrue($scopes->has(Scope::KEYS_READ));
        $this->assertTrue($scopes->has('keys:read'));
        $this->assertFalse($scopes->has(Scope::KEYS_DELETE));
    }

    public function testFromStringArray(): void
    {
        $scopes = AllowedScopes::fromStringArray(['keys:read', 'hashes:read']);

        $this->assertCount(2, $scopes->all());
        $this->assertTrue($scopes->has(Scope::KEYS_READ));
        $this->assertTrue($scopes->has(Scope::HASHES_READ));
    }

    public function testFromStringArrayWithInvalidScopeThrowsException(): void
    {
        $this->expectException(InvalidScopeException::class);

        AllowedScopes::fromStringArray(['invalid:scope']);
    }

    public function testToArray(): void
    {
        $scopes = new AllowedScopes([Scope::KEYS_READ, Scope::HASHES_READ]);

        $this->assertEquals(['keys:read', 'hashes:read'], $scopes->toArray());
    }

    public function testToString(): void
    {
        $scopes = new AllowedScopes([Scope::KEYS_READ, Scope::HASHES_READ]);

        $this->assertEquals('keys:read hashes:read', (string) $scopes);
    }

    public function testSeparator(): void
    {
        $scopes = new AllowedScopes([Scope::KEYS_READ, Scope::HASHES_READ]);
        $scopes->separator(',');

        $this->assertEquals('keys:read,hashes:read', (string) $scopes);
    }
}
