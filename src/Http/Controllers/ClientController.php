<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ProvisionClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;

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
            $createKeyResponseDTO = app(ProvisionClientUseCase::class)
                ->execute($clientId, $request->input('provision_token'));
        } catch (ClientException $e) {
            return response()->json(['error' => __('Failed to provision client: :message', ['message' => $e->getMessage()])], 422);
        }

        Event::dispatch('vault.client.provisioned', [$clientId, $createKeyResponseDTO->keyId]);

        return response()->json($createKeyResponseDTO->toArray(), 201);
    }
}
