<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Vault\Hash;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Hash;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\HashId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\ValueObjects\UserId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class HashTest extends TestCase
{
    private Hash $hash;

    private HashId $hashId;

    private UserId $userId;

    private string $hashValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hashId = new HashId(Uuid::uuid4()->toString());
        $this->userId = new UserId(Uuid::uuid4()->toString());
        $this->hashValue = 'hashed-value-123456';

        $this->hash = new Hash(
            $this->hashId,
            $this->userId,
            $this->hashValue,
            1,
            false,
            null
        );
    }

    public function testHashCreation(): void
    {
        $this->assertEquals($this->hashId->value(), $this->hash->hashId());
        $this->assertEquals($this->userId->value(), $this->hash->userId());
        $this->assertEquals($this->hashValue, $this->hash->hash());
        $this->assertEquals(1, $this->hash->version());
        $this->assertFalse($this->hash->isRevoked());
        $this->assertNull($this->hash->revokedAt());
    }

    public function testHashCreationWithRevoked(): void
    {
        $revokedAt = new DateTimeImmutable('2023-01-01');

        $revokedHash = new Hash(
            $this->hashId,
            $this->userId,
            $this->hashValue,
            1,
            true,
            $revokedAt
        );

        $this->assertEquals($this->hashId->value(), $revokedHash->hashId());
        $this->assertEquals($this->userId->value(), $revokedHash->userId());
        $this->assertEquals($this->hashValue, $revokedHash->hash());
        $this->assertEquals(1, $revokedHash->version());
        $this->assertTrue($revokedHash->isRevoked());
        $this->assertEquals($revokedAt, $revokedHash->revokedAt());
    }
}
