<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Shared\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Exceptions\IdException;
use Ramsey\Uuid\Uuid;

class Id
{
    public readonly string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid7()->toString();

        if (! Uuid::isValid($this->value)) {
            throw IdException::invalidUuid($this->value);
        }
    }
}
