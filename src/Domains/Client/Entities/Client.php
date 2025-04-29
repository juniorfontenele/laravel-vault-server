<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Client\Entities;

use JuniorFontenele\LaravelVaultServer\Domains\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects\ProvisionToken;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\ValueObjects\Id;

class Client
{
    public function __construct(
        protected Id $id,
        public string $name,
        public AllowedScopes $allowedScopes,
        public bool $isActive = true,
        public ?string $description = null,
        public ?ProvisionToken $provisionToken = null,
        public ?\DateTimeImmutable $provisionedAt = null,
    ) {
        //
    }

    public function id(): string
    {
        return $this->id->value;
    }

    public function provisionToken(): ?string
    {
        return $this->provisionToken?->__toString();
    }

    /**
     * @return string[]
     */
    public function scopes(): array
    {
        return $this->allowedScopes->toArray();
    }

    public function isProvisioned(): bool
    {
        return ! is_null($this->provisionedAt);
    }

    public function isNotProvisioned(): bool
    {
        return ! $this->isProvisioned();
    }

    public function provision(): void
    {
        if ($this->isProvisioned()) {
            throw ClientException::alreadyProvisioned($this->id());
        }

        $this->provisionToken = null;
        $this->provisionedAt = new \DateTimeImmutable();
    }

    public function reprovision(): void
    {
        $this->provisionToken = new ProvisionToken();
        $this->provisionedAt = null;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
