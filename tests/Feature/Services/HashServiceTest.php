<?php

declare(strict_types = 1);

use Illuminate\Hashing\Argon2IdHasher;
use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashStored;
use JuniorFontenele\LaravelVaultServer\Events\Hash\HashVerified;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\HashStoreException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Hash\RehashNeededException;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Services\HashService;
use JuniorFontenele\LaravelVaultServer\Services\PepperService;

beforeEach(function () {
    Hash::query()->delete();
    \JuniorFontenele\LaravelVaultServer\Models\Pepper::query()->delete();
    \JuniorFontenele\LaravelVaultServer\Models\Pepper::create([
        'version' => 1,
        'value' => 'pepper',
        'is_revoked' => false,
    ]);
    // Remove o hidden para value para facilitar o teste
    \JuniorFontenele\LaravelVaultServer\Models\Pepper::unsetEventDispatcher();
});

uses(\JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

describe('HashService', function () {
    it('stores and verifies a hash', function () {
        Event::fake();
        $service = new HashService(app(\JuniorFontenele\LaravelVaultServer\Services\PepperService::class), new Argon2IdHasher());
        $userId = 'user-1';
        $service->store($userId, 'password');
        expect(Hash::where('user_id', $userId)->first())->not()->toBeNull();
        expect($service->verify($userId, 'password'))->toBeTrue();
        Event::assertDispatched(HashStored::class);
        Event::assertDispatched(HashVerified::class);
    });

    it('throws HashStoreException on store failure', function () {
        $service = new HashService(app(\JuniorFontenele\LaravelVaultServer\Services\PepperService::class), new Argon2IdHasher());
        $this->expectException(HashStoreException::class);
        $service->store('', '');
    });

    it('throws RehashNeededException if needs rehash', function () {
        $service = new HashService(app(\JuniorFontenele\LaravelVaultServer\Services\PepperService::class), new Argon2IdHasher());
        $userId = 'user-2';
        $service->store($userId, 'password');
        // Simula needs_rehash
        Hash::where('user_id', $userId)->update(['needs_rehash' => true]);
        $this->expectException(RehashNeededException::class);
        $service->verify($userId, 'password');
    });

    it('deletes a hash', function () {
        Event::fake();
        $service = new HashService(app(\JuniorFontenele\LaravelVaultServer\Services\PepperService::class), new Argon2IdHasher());
        $userId = 'user-3';
        $service->store($userId, 'password');
        $service->delete($userId);
        expect(Hash::where('user_id', $userId)->first())->toBeNull();
        Event::assertDispatched(HashDeleted::class);
    });
});
