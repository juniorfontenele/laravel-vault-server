<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientIdException;
use Ramsey\Uuid\Uuid;

class ClientId
{
    public readonly string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid7()->toString();

        if (! Uuid::isValid($this->value)) {
            throw ClientIdException::invalidClientId($this->value);
        }
    }
}
