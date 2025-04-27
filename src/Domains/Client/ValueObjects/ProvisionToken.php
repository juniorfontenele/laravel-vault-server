<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Client\ValueObjects;

class ProvisionToken
{
    protected string $value;

    public function __construct()
    {
        $this->value = bin2hex(random_bytes(16));
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
