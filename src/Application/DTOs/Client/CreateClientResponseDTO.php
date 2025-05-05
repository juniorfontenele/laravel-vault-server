<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\DTOs\Client;

class CreateClientResponseDTO
{
    /**
     * @param string $clientId
     * @param string $name
     * @param ?string $description
     * @param string[] $allowedScopes
     * @param string $provisionToken
     */
    public function __construct(
        public string $clientId,
        public string $name,
        public array $allowedScopes,
        public string $provisionToken,
        public ?string $description,
    ) {
        //
    }

    /**
     * @return array{client_id: string, name: string, allowed_scopes: array<string>, description: ?string, provision_token: string}
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'name' => $this->name,
            'allowed_scopes' => implode(',', $this->allowedScopes),
            'description' => $this->description,
            'provision_token' => $this->provisionToken,
        ];
    }
}
