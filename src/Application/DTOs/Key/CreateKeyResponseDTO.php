<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Key;

use DateTimeImmutable;

class CreateKeyResponseDTO
{
    public function __construct(
        public readonly string $keyId,
        public readonly string $clientId,
        public readonly string $publicKey,
        public readonly string $privateKey,
        public readonly int $version,
        public readonly DateTimeImmutable $validFrom,
        public readonly DateTimeImmutable $validUntil,
    ) {
    }

    /**
     * @return array{key_id: string, client_id: string, public_key: string, private_key: string, version: int, valid_from: string, valid_until: string}
     */
    public function toArray(): array
    {
        return [
            'key_id' => $this->keyId,
            'client_id' => $this->clientId,
            'public_key' => $this->publicKey,
            'private_key' => $this->privateKey,
            'version' => $this->version,
            'valid_from' => $this->validFrom->format('Y-m-d H:i:s'),
            'valid_until' => $this->validUntil->format('Y-m-d H:i:s'),
        ];
    }
}
