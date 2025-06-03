<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Hash as HashFacade;
use JuniorFontenele\LaravelVaultServer\Models\Client;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Client::query()->delete();
});

it('provisions a client', function () {
    $client = Client::factory()->create();
    $token = 'abcd1234abcd1234abcd1234abcd1234';
    $client->provision_token = HashFacade::make($token);
    $client->save();

    $response = $this->postJson(route('vault.client.provision', $client->id), [
        'provision_token' => $token,
    ]);

    $response->assertCreated();
    $response->assertJson(['client_id' => $client->id]);
});
