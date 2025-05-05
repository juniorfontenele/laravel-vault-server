<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Client;

class CreateClientDTO
{
    /**
     * @param string $name
     * @param string[] $allowedScopes
     * @param string|null $description
     */
    public function __construct(
        public readonly string $name,
        public readonly array $allowedScopes,
        public readonly ?string $description = null,
    ) {
        //
    }
}
