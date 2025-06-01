<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Actions\Client;

use Illuminate\Validation\Rules\Enum;
use JuniorFontenele\LaravelVaultServer\Artifacts\NewClient;
use JuniorFontenele\LaravelVaultServer\Concerns\HasValidation;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientCreated;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

class CreateClientAction
{
    use HasValidation;

    public function __construct(protected GenerateProvisionTokenAction $generateProvisionTokenAction)
    {
        //
    }

    public function execute(string $name, array $allowedScopes, ?string $description = null): NewClient
    {
        $provisionToken = $this->generateProvisionTokenAction->execute();

        $validated = $this->validate([
            'name' => $name,
            'allowed_scopes' => $allowedScopes,
            'description' => $description,
            'provision_token' => $provisionToken,
        ]);

        $client = ClientModel::create($validated->toArray());

        event(new ClientCreated($client));

        return new NewClient(
            $client,
            $validated->provision_token,
        );
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'allowed_scopes' => ['required', 'array'],
            'allowed_scopes.*' => ['required', new Enum(Scope::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'provision_token' => ['required', 'string', 'size:64'],
        ];
    }
}
