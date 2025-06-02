<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthenticatedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotFoundException;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;

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

        try {
            $keyPair = VaultClientManager::provisionClient(
                $clientId,
                $request->input('provision_token')
            );
        } catch (ClientNotFoundException $e) {
            return response()->json(['error' => __('Client not found :clientId', ['clientId' => $clientId])], 404);
        } catch (ClientNotAuthenticatedException $e) {
            return response()->json(['error' => __('Failed to authenticate client')], 401);
        } catch (\Exception $e) {
            Log::error('Failed to provision client', [
                'clientId' => $clientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => __('An unexpected error occurred')], 400);
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
