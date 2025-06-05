<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthenticatedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotFoundException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;

class ClientController extends Controller
{
    public function provision(Request $request, string $clientId)
    {
        $validated = Validator::make($request->all(), [
            'provision_token' => 'required|string|size:32',
        ])->validate();

        try {
            $keyPair = VaultClientManager::provisionClient(
                $clientId,
                $validated['provision_token']
            );
        } catch (ClientNotFoundException $e) {
            return response()->json(['message' => 'Client not found'], 404);
        } catch (ClientNotAuthenticatedException $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $response = [
            'key_id' => $keyPair->key->id,
            'public_key' => $keyPair->key->public_key,
            'private_key' => $keyPair->private_key,
            'version' => $keyPair->key->version,
            'valid_from' => $keyPair->key->valid_from->toIso8601String(),
            'valid_until' => $keyPair->key->valid_until->toIso8601String(),
            'client_id' => $clientId,
        ];

        return response()->json($response, 201);
    }
}
