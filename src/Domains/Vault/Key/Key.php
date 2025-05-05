<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;

class Key
{
    public function __construct(
        protected KeyId $keyId,
        protected ClientId $clientId,
        protected PublicKey $publicKey,
        protected int $version,
        protected DateTimeImmutable $validFrom,
        protected DateTimeImmutable $validUntil,
        protected bool $isRevoked = false,
        protected ?DateTimeImmutable $revokedAt = null,
    ) {
        //
    }

    public function keyId(): string
    {
        return $this->keyId->value();
    }

    public function clientId(): string
    {
        return $this->clientId->value();
    }

    public function publicKey(): string
    {
        return $this->publicKey->value();
    }

    public function version(): int
    {
        return $this->version;
    }

    public function validFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validUntil(): DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function isRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function revokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function revoke(): void
    {
        $this->isRevoked = true;
        $this->revokedAt = new DateTimeImmutable();
    }
}
