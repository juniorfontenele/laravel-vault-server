<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Client;

class ClientResponseDTO
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $name,
        public readonly array $allowedScopes,
        public readonly ?string $description,
    ) {
    }

    /**
     * @return array{client_id: string, name: string, allowed_scopes: array<string>, description: ?string}
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'name' => $this->name,
            'allowed_scopes' => implode(',', $this->allowedScopes),
            'description' => $this->description,
        ];
    }
}
