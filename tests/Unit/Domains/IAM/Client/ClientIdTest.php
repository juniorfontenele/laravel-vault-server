<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientIdException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class ClientIdTest extends TestCase
{
    public function testCreateClientIdWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $clientId = new ClientId($uuid);

        $this->assertEquals($uuid, $clientId->value);
    }

    public function testCreateClientIdWithoutUuidGeneratesOne(): void
    {
        $clientId = new ClientId();

        $this->assertTrue(Uuid::isValid($clientId->value));
    }

    public function testCreateClientIdWithInvalidUuidThrowsException(): void
    {
        $this->expectException(ClientIdException::class);

        new ClientId('not-a-valid-uuid');
    }
}
