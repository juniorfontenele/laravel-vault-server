<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Events\Key\ExpiredKeysCleanedUp;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyCreated;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRetrieved;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRevoked;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRotated;
use JuniorFontenele\LaravelVaultServer\Events\Key\RevokedKeysCleanedUp;
use JuniorFontenele\LaravelVaultServer\Exceptions\Key\KeyNotFoundException;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Services\KeyPairService;

covers(KeyPairService::class);

beforeEach(function () {
    Key::query()->delete();
    Client::query()->delete();
});

describe('KeyPairService', function () {
    it('creates a key and dispatches event', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $newKey = $service->create($client->id, 2048, 365);
        expect($newKey)->not()->toBeNull();
        Event::assertDispatched(KeyCreated::class);
    });

    it('rotates a key and dispatches event', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $newKey = $service->create($client->id, 2048, 365);
        $rotated = $service->rotate($newKey->key->id, 2048, 365);
        expect($rotated)->not()->toBeNull();
        Event::assertDispatched(KeyRotated::class);
    });

    it('throws KeyNotFoundException on rotate with invalid id', function () {
        $service = app(KeyPairService::class);
        $this->expectException(KeyNotFoundException::class);
        $service->rotate('invalid', 2048, 365);
    });

    it('revokes a key and dispatches event', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $newKey = $service->create($client->id, 2048, 365);
        $service->revoke($newKey->key->id);
        $revoked = Key::find($newKey->key->id);
        expect($revoked->is_revoked)->toBeTrue();
        Event::assertDispatched(KeyRevoked::class);
    });

    it('finds a key by id and dispatches event', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $revokedKey = Key::factory()->create([
            'client_id' => $client->id,
            'is_revoked' => true,
        ]);
        $newKey = $service->create($client->id, 2048, 365);
        $found = $service->get($newKey->key->id);
        expect($found->id)->toBe($newKey->key->id);
        expect(fn () => $service->get($revokedKey->id))->toThrow(KeyNotFoundException::class);
        Event::assertDispatched(KeyRetrieved::class);
    });

    it('cleans up expired keys', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $expired = Key::factory()->create([
            'client_id' => $client->id,
            'valid_from' => now()->subDays(10),
            'valid_until' => now()->subDay(),
        ]);
        $active = Key::factory()->create([
            'client_id' => $client->id,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);
        $cleaned = $service->cleanupExpiredKeys();
        expect($cleaned->pluck('id')->all())
            ->toContain($expired->id)
            ->not()->toContain($active->id);
        Event::assertDispatched(ExpiredKeysCleanedUp::class);
    });

    it('cleans up revoked keys', function () {
        Event::fake();
        $service = app(KeyPairService::class);
        $client = Client::factory()->create();
        $revoked = Key::factory()->create([
            'client_id' => $client->id,
            'is_revoked' => true,
        ]);
        $active = Key::factory()->create([
            'client_id' => $client->id,
            'is_revoked' => false,
        ]);
        $cleaned = $service->cleanupRevokedKeys();
        expect($cleaned->pluck('id')->all())
            ->toContain($revoked->id)
            ->not()->toContain($active->id);
        Event::assertDispatched(RevokedKeysCleanedUp::class);
    });
});
