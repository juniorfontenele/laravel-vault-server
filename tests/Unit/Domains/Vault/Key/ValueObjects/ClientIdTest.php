<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Vault\Key\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\ClientIdException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class ClientIdTest extends TestCase
{
    public function testCreateClientIdWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $clientId = new ClientId($uuid);

        $this->assertEquals($uuid, $clientId->value());
        $this->assertEquals($uuid, (string) $clientId);
    }

    public function testCreateClientIdWithInvalidUuidThrowsException(): void
    {
        $this->expectException(ClientIdException::class);

        new ClientId('not-a-valid-uuid');
    }
}
