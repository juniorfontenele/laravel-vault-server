<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Key\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Key\Exceptions\ClientIdException;
use Ramsey\Uuid\Uuid;

class ClientId
{
    public function __construct(protected string $value)
    {
        if (! Uuid::isValid($this->value)) {
            throw ClientIdException::invalidClientId($this->value);
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
