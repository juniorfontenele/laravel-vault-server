<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Key;

class CreateKeyDTO
{
    public function __construct(
        public readonly string $clientId,
        public readonly int $keySize = 2048,
        public readonly int $expiresIn = 365,
    ) {
    }
}
