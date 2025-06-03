<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyCreated;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRetrieved;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRevoked;
use JuniorFontenele\LaravelVaultServer\Events\Key\KeyRotated;
use JuniorFontenele\LaravelVaultServer\Exceptions\Key\KeyNotFoundException;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Services\KeyPairService;

beforeEach(function () {
    Key::query()->delete();
});

uses(\JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

describe('KeyPairService', function () {
    it('creates a key and dispatches event', function () {
        Event::fake();
        $service = new KeyPairService();
        $newKey = $service->create('client-1', 2048, 365);
        expect($newKey)->not()->toBeNull();
        Event::assertDispatched(KeyCreated::class);
    });

    it('rotates a key and dispatches event', function () {
        Event::fake();
        $service = new KeyPairService();
        $newKey = $service->create('client-2', 2048, 365);
        $rotated = $service->rotate($newKey->key->id, 2048, 365);
        expect($rotated)->not()->toBeNull();
        Event::assertDispatched(KeyRotated::class);
    });

    it('throws KeyNotFoundException on rotate with invalid id', function () {
        $service = new KeyPairService();
        $this->expectException(KeyNotFoundException::class);
        $service->rotate('invalid', 2048, 365);
    });

    it('revokes a key and dispatches event', function () {
        Event::fake();
        $service = new KeyPairService();
        $newKey = $service->create('client-3', 2048, 365);
        $service->revoke($newKey->key->id);
        $revoked = Key::find($newKey->key->id);
        expect($revoked->is_revoked)->toBeTrue();
        Event::assertDispatched(KeyRevoked::class);
    });

    it('finds a key by id and dispatches event', function () {
        Event::fake();
        $service = new KeyPairService();
        $newKey = $service->create('client-4', 2048, 365);
        $found = $service->findByKeyId($newKey->key->id);
        expect($found->id)->toBe($newKey->key->id);
        Event::assertDispatched(KeyRetrieved::class);
    });
});
