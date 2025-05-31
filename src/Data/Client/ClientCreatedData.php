<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Carbon\CarbonImmutable;
use JuniorFontenele\LaravelVaultServer\Data\AbstractData;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

class ClientCreatedData extends AbstractData
{
    /**
     * @param string $id
     * @param string $name
     * @param Scope[] $allowed_scopes
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'allowed_scopes' => array_map(fn (Scope $scope) => $scope->value, $this->allowed_scopes),
            'provision_token' => $this->provision_token,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            allowed_scopes: array_map(
                fn ($scope) => Scope::from($scope),
                $data['allowed_scopes']
            ),
            provision_token: $data['provision_token'],
            description: $data['description'] ?? null,
            created_at: isset($data['created_at']) ? CarbonImmutable::parse($data['created_at']) : null,
        );
    }
}
