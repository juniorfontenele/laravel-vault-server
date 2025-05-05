<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Vault\Key;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class KeyTest extends TestCase
{
    private Key $key;

    private KeyId $keyId;

    private ClientId $clientId;

    private PublicKey $publicKey;

    private DateTimeImmutable $validFrom;

    private DateTimeImmutable $validUntil;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyId = $this->createMock(KeyId::class);
        $this->keyId->method('value')->willReturn(Uuid::uuid4()->toString());

        $this->clientId = new ClientId(Uuid::uuid4()->toString());
        $this->publicKey = $this->createMock(PublicKey::class);
        $this->publicKey->method('value')->willReturn('ssh-rsa AAAAB3NzaC1yc2EAAAAD test-key');

        $this->validFrom = new DateTimeImmutable();
        $this->validUntil = (new DateTimeImmutable())->modify('+1 year');

        $this->key = new Key(
            $this->keyId,
            $this->clientId,
            $this->publicKey,
            1,
            $this->validFrom,
            $this->validUntil
        );
    }

    public function testKeyCreation(): void
    {
        $this->assertEquals($this->keyId->value(), $this->key->keyId());
        $this->assertEquals($this->clientId->value(), $this->key->clientId());
        $this->assertEquals($this->publicKey->value(), $this->key->publicKey());
        $this->assertEquals(1, $this->key->version());
        $this->assertEquals($this->validFrom, $this->key->validFrom());
        $this->assertEquals($this->validUntil, $this->key->validUntil());
        $this->assertFalse($this->key->isRevoked());
        $this->assertNull($this->key->revokedAt());
    }

    public function testRevokeKey(): void
    {
        $this->assertFalse($this->key->isRevoked());
        $this->assertNull($this->key->revokedAt());

        $this->key->revoke();

        $this->assertTrue($this->key->isRevoked());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->key->revokedAt());
    }
}
