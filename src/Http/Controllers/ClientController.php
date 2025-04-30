<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Http\Resources\KeyResource;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\ClientModel;

class ClientController
{
    public function provision(Request $request, string $clientId)
    {
        $validator = Validator::make($request->all(), [
            'provision_token' => 'required|string|size:32',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $client = ClientModel::query()
            ->active()
            ->where('id', $clientId)
            ->firstOrFail();

        if ($client->provision_token === null) {
            return response()->json(['error' => 'Client is already provisioned'], 403);
        }

        $provisionToken = $request->input('provision_token');

        if (! password_verify($provisionToken, $client->provision_token)) {
            return response()->json(['error' => 'Invalid provision token'], 403);
        }

        return DB::transaction(function () use ($client) {
            [$key, $privateKey] = VaultKey::createKeyForClient($client);

            $client->update([
                'provision_token' => null,
            ]);

            Event::dispatch('vault.client.provisioned', [$client, $key]);

            $key->private_key = $privateKey;

            return $key->toResource(KeyResource::class);
        });
    }
}
