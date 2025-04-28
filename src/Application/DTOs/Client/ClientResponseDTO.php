<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Client;

class ClientResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $allowedScopes,
        public readonly ?string $description,
    ) {
    }
}
