<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Illuminate\Validation\Rules\Enum;
use JuniorFontenele\LaravelVaultServer\Data\AbstractData;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

class CreateClientData extends AbstractData
{
    public string $provision_token;

    /**
     * @param string $name
     * @param Scope[] $allowed_scopes
     * @param string|null $description
     */
    public function __construct(
        public string $name,
        public array $allowed_scopes,
        public ?string $description = null,
    ) {
        $this->provision_token = bin2hex(random_bytes(16));
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'allowed_scopes' => array_map(fn (Scope $scope) => $scope->value, $this->allowed_scopes),
            'description' => $this->description,
            'provision_token' => $this->provision_token,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            allowed_scopes: $data['allowed_scopes'],
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validation rules for the client creation data.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'allowed_scopes' => ['required', 'array'],
            'allowed_scopes.*' => [new Enum(Scope::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'provision_token' => ['required', 'string', 'size:32'],
        ];
    }
}
