<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Vault\Hash;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions\HashIdException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\HashId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class HashIdTest extends TestCase
{
    public function testCreateHashIdWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $hashId = new HashId($uuid);

        $this->assertEquals($uuid, $hashId->value());
        $this->assertEquals($uuid, (string) $hashId);
    }

    public function testCreateHashIdWithoutUuidGeneratesOne(): void
    {
        $hashId = new HashId();

        $this->assertTrue(Uuid::isValid($hashId->value()));
    }

    public function testCreateHashIdWithInvalidUuidThrowsException(): void
    {
        $this->expectException(HashIdException::class);

        new HashId('not-a-valid-uuid');
    }
}
