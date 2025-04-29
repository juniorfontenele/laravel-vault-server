<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Shared\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Exceptions\IdException;
use Ramsey\Uuid\Uuid;

class Id
{
    public function __construct(public readonly ?string $value = null)
    {
        if (is_null($this->value)) {
            $this->value = Uuid::uuid7()->toString();
        }

        if (! Uuid::isValid($this->value)) {
            throw IdException::invalidUuid($this->value);
        }
    }
}
