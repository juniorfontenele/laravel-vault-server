<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client;

use JuniorFontenele\LaravelVaultServer\Exceptions\ClientIdException;
use Ramsey\Uuid\Uuid;

class ClientId
{
    protected readonly string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid7()->toString();

        if (! Uuid::isValid($this->value())) {
            throw ClientIdException::invalidClientId($this->value());
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
