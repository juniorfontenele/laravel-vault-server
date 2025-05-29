<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Carbon\CarbonImmutable;

final readonly class ClientData
{
    /**
     * @param string $id
     * @param string $name
     * @param string[] $allowed_scopes
     * @param string|null $description
     * @param CarbonImmutable|null $provisioned_at
     * @param CarbonImmutable|null $created_at
     * @param CarbonImmutable|null $updated_at
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $allowed_scopes,
        public ?string $description = null,
        public ?CarbonImmutable $provisioned_at = null,
        public ?CarbonImmutable $created_at = null,
        public ?CarbonImmutable $updated_at = null,
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
            description: $data['description'] ?? null,
            provisioned_at: $data['provisioned_at'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }
}
