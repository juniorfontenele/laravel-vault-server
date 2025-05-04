<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Key;

class KeyResponseDTO
{
    public function __construct(
        public readonly string $keyId,
        public readonly string $clientId,
        public readonly string $publicKey,
        public readonly int $version,
        public readonly \DateTimeImmutable $validFrom,
        public readonly \DateTimeImmutable $validUntil,
        public readonly bool $isRevoked,
        public readonly ?\DateTimeImmutable $revokedAt,
    ) {
    }

    /**
     * @return array{key_id: string, client_id: string, public_key: string, version: int, valid_from: string, valid_until: string, is_revoked: bool, revoked_at: string}
     */
    public function toArray(): array
    {
        return [
            'key_id' => $this->keyId,
            'client_id' => $this->clientId,
            'public_key' => $this->publicKey,
            'version' => $this->version,
            'valid_from' => $this->validFrom->format('Y-m-d H:i:s'),
            'valid_until' => $this->validUntil->format('Y-m-d H:i:s'),
            'is_revoked' => $this->isRevoked,
            'revoked_at' => $this->revokedAt->format('Y-m-d H:i:s'),
        ];
    }
}
