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

    /**
     * @return array{id: string, name: string, allowed_scopes: array<string>, description: ?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'allowed_scopes' => implode(',', $this->allowedScopes),
            'description' => $this->description,
        ];
    }
}
