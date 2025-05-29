<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data\Client;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rules\Enum;
use JsonSerializable;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

final readonly class CreateClientData implements JsonSerializable, Arrayable
{
    public string $provision_token;

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
        $this->provision_token = bin2hex(random_bytes(16));

        validator($this->toArray(), $this->rules())->validate();
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
