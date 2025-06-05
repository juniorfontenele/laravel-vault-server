<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Hash as HashFacade;
use Illuminate\Support\Str;
use JuniorFontenele\LaravelVaultServer\Http\Controllers\ClientController;
use JuniorFontenele\LaravelVaultServer\Models\Client;

covers(ClientController::class);

beforeEach(function () {
    Client::query()->delete();
});

describe('ClientController', function () {
    it('provisions a client', function () {
        $client = Client::factory()->create();
        $token = 'abcd1234abcd1234abcd1234abcd1234';
        $client->provision_token = HashFacade::make($token);
        $client->save();

        $response = $this->postJson(route('vault.client.provision', $client->id), [
            'provision_token' => $token,
        ]);

        $response->assertCreated();
        $response->assertJson([
            'client_id' => $client->id,
            'version' => 1,
        ]);
        $response->assertJsonStructure([
            'key_id',
            'client_id',
            'public_key',
            'private_key',
            'version',
            'valid_from',
            'valid_until',
        ]);
    });

    it('cannot provision a client with incorrect provision token', function () {
        $client = Client::factory()->create();
        $token = 'my-token';
        $client->provision_token = HashFacade::make($token);
        $client->save();

        $response = $this->postJson(route('vault.client.provision', $client->id), [
            'provision_token' => Str::random(32),
        ]);

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthorized']);
        expect(Client::find($client->id)->keys()->count())->toBe(0);
    });

    it('cannot provision a client with missing token', function () {
        $client = Client::factory()->create();
        $response = $this->postJson(route('vault.client.provision', $client->id));

        $response->assertUnprocessable();
        $response->assertInvalid(['provision_token']);
        expect(Client::find($client->id)->keys()->count())->toBe(0);
    });

    it('fails to provision a non-existent client', function () {
        $response = $this->postJson(route('vault.client.provision', 'non-existent-client'), [
            'provision_token' => 'abcd1234abcd1234abcd1234abcd1234',
        ]);

        $response->assertNotFound();
        $response->assertJson(['message' => 'Client not found']);
    });

    it('fails to provision a client with an invalid token', function () {
        $client = Client::factory()->create();
        $response = $this->postJson(route('vault.client.provision', $client->id), [
            'provision_token' => 'invalid-token',
        ]);

        $response->assertUnprocessable();
        $response->assertInvalid(['provision_token' => 'The provision token field must be 32 characters.']);
        expect(Client::find($client->id)->keys()->count())->toBe(0);
    });
});
