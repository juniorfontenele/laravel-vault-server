<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

final readonly class CreateClientData
{
    /**
     * @param string $name
     * @param string[] $allowed_scopes
     * @param string|null $description
     */
    public function __construct(
        public string $name,
        public array $allowed_scopes,
        public ?string $description = null,
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
            name: $data['name'],
            allowed_scopes: $data['allowed_scopes'],
            description: $data['description'] ?? null,
        );
    }
}
