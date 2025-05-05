<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\ValueObjects\UserId;

class Hash
{
    public function __construct(
        protected readonly HashId $hashId,
        protected readonly UserId $userId,
        protected readonly string $hash,
        protected readonly int $version,
        protected readonly bool $isRevoked,
        protected readonly ?\DateTimeImmutable $revokedAt,
    ) {
        //
    }

    public function hashId(): string
    {
        return $this->hashId->value();
    }

    public function userId(): string
    {
        return $this->userId->value();
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function isRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function revokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }
}
