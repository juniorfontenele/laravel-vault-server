<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions\UserIdException;
use Ramsey\Uuid\Uuid;

class UserId
{
    public function __construct(protected string $value)
    {
        if (! Uuid::isValid($value)) {
            throw UserIdException::invalidUserId($value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
