<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\KeyIdException;
use Ramsey\Uuid\Uuid;

class KeyId
{
    protected string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid7()->toString();

        if (! Uuid::isValid($this->value)) {
            throw KeyIdException::invalidKeyId($this->value);
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
