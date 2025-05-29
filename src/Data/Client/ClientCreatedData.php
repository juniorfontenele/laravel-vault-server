<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Carbon\CarbonImmutable;

final readonly class ClientCreatedData
{
    /**
     * @param string $id
     * @param string $name
     * @param string[] $allowed_scopes
     * @param string $provision_token
     * @param string|null $description
     * @param CarbonImmutable|null $created_at
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $allowed_scopes,
        public string $provision_token,
        public ?string $description = null,
        public ?CarbonImmutable $created_at = null,
    ) {
        //
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            allowed_scopes: $data['allowed_scopes'],
            provision_token: $data['provision_token'],
            description: $data['description'] ?? null,
            created_at: $data['created_at'] ?? null,
        );
    }
}
