<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Vault\Hash\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions\UserIdException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\ValueObjects\UserId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class UserIdTest extends TestCase
{
    public function testCreateUserIdWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $userId = new UserId($uuid);

        $this->assertEquals($uuid, $userId->value());
        $this->assertEquals($uuid, (string) $userId);
    }

    public function testCreateUserIdWithInvalidUuidThrowsException(): void
    {
        $this->expectException(UserIdException::class);

        new UserId('not-a-valid-uuid');
    }
}
