<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelSecureJwt\CustomClaims;
use JuniorFontenele\LaravelSecureJwt\Facades\SecureJwt;
use JuniorFontenele\LaravelSecureJwt\JwtKey;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Key;

beforeEach(function () {
    Key::query()->delete();
    Client::query()->delete();
});

describe('KmsController', function () {
    it('returns a key by id', function () {
        $client = Client::factory()->create();
        $newKey = VaultKey::create($client->id, 2048, 365);

        $response = $this->getJson(route('vault.kms.get', $newKey->key->id));
        $response->assertOk();
        $response->assertJson(['key_id' => $newKey->key->id]);
    });

    it('rotates a key', function () {
        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.kms.rotate'));

        $response->assertCreated();
        $response->assertJsonStructure([
            'key_id',
            'public_key',
            'private_key',
            'client_id',
            'version',
            'valid_until',
            'valid_from',
        ]);
        $response->assertJson(['version' => 2]);

        expect(Key::query()->count())->toBe(2);
    });

    it('cannot rotate another key', function () {
        $client = Client::factory()->create();
        $newKey = VaultKey::create($client->id, 2048, 365);

        $otherClient = Client::factory()->create();
        $otherNewKey = VaultKey::create($otherClient->id, 2048, 365);

        $token = SecureJwt::encode(
            new CustomClaims(),
            new JwtKey(
                $newKey->key->id,
                $newKey->private_key,
                $newKey->key->algorithm
            )
        );

        $response = $this->withToken($token)->postJson(route('vault.kms.rotate'), [
            'client_id' => $otherNewKey->key->client_id,
        ]);

        expect(Key::query()->count())->toBe(3);
        $response->assertCreated();
        $response->assertJson([
            'client_id' => $client->id,
            'version' => 2,
        ]);
    });
});
