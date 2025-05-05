<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client\Enums;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\InvalidScopeException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ScopeTest extends TestCase
{
    public function testGetLabel(): void
    {
        $this->assertEquals('Read keys', Scope::KEYS_READ->getLabel());
        $this->assertEquals('Rotate keys', Scope::KEYS_ROTATE->getLabel());
        $this->assertEquals('Delete keys', Scope::KEYS_DELETE->getLabel());
        $this->assertEquals('Read hashes', Scope::HASHES_READ->getLabel());
        $this->assertEquals('Create hashes', Scope::HASHES_CREATE->getLabel());
        $this->assertEquals('Delete hashes', Scope::HASHES_DELETE->getLabel());
    }

    public function testToArray(): void
    {
        $scopesArray = Scope::toArray();

        $this->assertCount(6, $scopesArray);
        $this->assertEquals('Read keys', $scopesArray['keys:read']);
        $this->assertEquals('Rotate keys', $scopesArray['keys:rotate']);
        $this->assertEquals('Delete keys', $scopesArray['keys:delete']);
        $this->assertEquals('Read hashes', $scopesArray['hashes:read']);
        $this->assertEquals('Create hashes', $scopesArray['hashes:create']);
        $this->assertEquals('Delete hashes', $scopesArray['hashes:delete']);
    }

    public function testFromString(): void
    {
        $this->assertEquals(Scope::KEYS_READ, Scope::fromString('keys:read'));
        $this->assertEquals(Scope::KEYS_ROTATE, Scope::fromString('keys:rotate'));
    }

    public function testFromStringWithInvalidScopeThrowsException(): void
    {
        $this->expectException(InvalidScopeException::class);

        Scope::fromString('invalid:scope');
    }
}
