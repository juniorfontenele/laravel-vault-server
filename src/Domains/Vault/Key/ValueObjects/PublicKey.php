<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\PublicKeyException;

class PublicKey
{
    protected string $value;

    public function __construct(string $value)
    {
        // Validate public key format
        if (! preg_match('/^-----BEGIN PUBLIC KEY-----\n.*\n-----END PUBLIC KEY-----$/s', $value)) {
            throw PublicKeyException::invalidPublicKey();
        }

        $this->value = $value;
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
