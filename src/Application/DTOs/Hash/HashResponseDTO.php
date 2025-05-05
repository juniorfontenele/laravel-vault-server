<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Hash;

class HashResponseDTO
{
    public function __construct(
        public readonly string $hashId,
        public readonly string $userId,
        public readonly string $hash,
        public readonly int $version,
        public readonly bool $isRevoked,
        public readonly ?\DateTimeImmutable $revokedAt,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'hash_id' => $this->hashId,
            'user_id' => $this->userId,
            'hash' => $this->hash,
            'version' => $this->version,
            'is_revoked' => $this->isRevoked,
            'revoked_at' => $this->revokedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
