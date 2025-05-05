<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\ProvisionToken;

class Client
{
    public function __construct(
        protected ClientId $clientId,
        protected string $name,
        protected AllowedScopes $allowedScopes,
        protected bool $isActive = true,
        protected ?string $description = null,
        protected ?ProvisionToken $provisionToken = null,
        protected ?\DateTimeImmutable $provisionedAt = null,
    ) {
        //
    }

    public function clientId(): string
    {
        return $this->clientId->value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function provisionToken(): ?string
    {
        return $this->provisionToken?->__toString();
    }

    public function provisionedAt(): ?\DateTimeImmutable
    {
        return $this->provisionedAt;
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

    public function provision(ProvisionToken|string $userProvidedToken): void
    {
        if ($this->isProvisioned()) {
            throw ClientException::alreadyProvisioned($this->clientId());
        }

        if (! $this->verifyProvisionToken($userProvidedToken)) {
            throw ClientException::invalidProvisionToken($this->clientId());
        }

        $this->provisionToken = null;
        $this->provisionedAt = new \DateTimeImmutable();
    }

    public function verifyProvisionToken(ProvisionToken|string $userProvidedToken): bool
    {
        return $this->provisionToken->verify($userProvidedToken);
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
