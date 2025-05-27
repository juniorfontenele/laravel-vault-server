<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

use JuniorFontenele\LaravelVaultServer\Data\Client\CreateClientData;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class CreateClientAction
{
    public function execute(CreateClientData $data): ClientModel
    {
        $validated = validator($data->toArray(), $this->rules())->validate();

        $client = new ClientModel();
        $client->name = $validated['name'];
        $client->allowed_scopes = $validated['allowed_scopes'];
        $client->description = $validated['description'];
        $client->provision_token = bin2hex(random_bytes(16));
        $client->save();

        return $client;
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
            'allowed_scopes.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
