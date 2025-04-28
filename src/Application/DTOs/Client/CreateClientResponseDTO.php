<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Client;

class CreateClientResponseDTO
{
    /**
     * @param string $id
     * @param string $name
     * @param ?string $description
     * @param string[] $allowedScopes
     * @param string $provisionToken
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $allowedScopes,
        public string $provisionToken,
        public ?string $description,
    ) {
        //
    }
}
