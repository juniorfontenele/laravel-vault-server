<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Carbon\CarbonImmutable;
use JuniorFontenele\LaravelVaultServer\Data\AbstractData;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

class ClientData extends AbstractData
{
    /**
     * @param string $id
     * @param string $name
     * @param Scope[] $allowed_scopes
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'allowed_scopes' => array_map(fn (Scope $scope) => $scope->value, $this->allowed_scopes),
            'description' => $this->description,
            'provisioned_at' => $this->provisioned_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
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
            description: $data['description'] ?? null,
            provisioned_at: isset($data['provisioned_at']) ? CarbonImmutable::parse($data['provisioned_at']) : null,
            created_at: isset($data['created_at']) ? CarbonImmutable::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? CarbonImmutable::parse($data['updated_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
