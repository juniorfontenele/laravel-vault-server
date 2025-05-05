<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects;

class ProvisionToken
{
    protected string $value;

    protected ?string $plainValue;

    public function __construct(?string $value = null)
    {
        if (is_null($value)) {
            $this->plainValue = bin2hex(random_bytes(16));

            $this->value = password_hash($this->plainValue, PASSWORD_BCRYPT);
        } else {
            $this->value = $value;

            $this->plainValue = null;
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function plainValue(): string
    {
        return $this->plainValue;
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public function verify(ProvisionToken|string $userProvidedToken): bool
    {
        if ($userProvidedToken instanceof ProvisionToken) {
            $userProvidedToken = $userProvidedToken->plainValue();
        }

        return password_verify($userProvidedToken, $this->value());
    }
}
