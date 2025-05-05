<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions\HashIdException;
use Ramsey\Uuid\Uuid;

class HashId
{
    protected string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid7()->toString();

        if (! Uuid::isValid($this->value)) {
            throw HashIdException::invalidHashId($this->value);
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
