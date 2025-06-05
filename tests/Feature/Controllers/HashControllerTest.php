<?php

declare(strict_types = 1);

use Illuminate\Support\Str;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;
use JuniorFontenele\LaravelVaultServer\Services\PepperService;

beforeEach(function () {
    Hash::query()->delete();
    Pepper::query()->delete();
    Pepper::create([
        'version' => 1,
        'value' => 'pepper',
        'is_revoked' => false,
    ]);

    $this->securePassword = Str::password(16, true, true, true);
});

describe('HashController', function () {
    it('fails validation storing password when password is missing', function () {
        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.store', ['userId' => 'user1']), []);

        expect($response->status())->toBe(422);
        $response->assertJsonValidationErrors(['password']);
    });

    it('fails validation verifying password when password is missing', function () {
        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.verify', ['userId' => 'user1']), []);

        expect($response->status())->toBe(422);
        $response->assertJsonValidationErrors(['password']);
    });

    it('fails store password when token is missing', function () {
        $response = $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => $this->securePassword]);
        $response->assertUnauthorized();
    });

    it('stores password with complexity requirements', function () {
        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => $this->securePassword]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'message',
        ]);

        expect(Hash::query()->count())->toBe(1);
        expect(Hash::query()->first()->user_id)->toBe('user1');
        expect(Hash::query()->first()->hash)->not->toBe($this->securePassword);
    });

    it('passes verification with correct password', function () {
        $this->updateAuthorizationHeaders();

        $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => $this->securePassword])
            ->assertCreated();

        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.verify', ['userId' => 'user1']), ['password' => $this->securePassword]);
        $response->assertOk();
        $response->assertJson(['message' => 'Authorized', 'needs_rehash' => false]);
    });

    it('fails verification with incorrect password', function () {
        $this->updateAuthorizationHeaders();

        $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => $this->securePassword])
            ->assertCreated();

        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.verify', ['userId' => 'user1']), ['password' => 'wrong']);
        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthorized']);
    });

    it('authenticates and returns needs_rehash true when rehash is needed', function () {
        $this->updateAuthorizationHeaders();

        $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => $this->securePassword])
            ->assertCreated();

        // Simulate a rehash needed scenario
        app(PepperService::class)->rotatePepper();

        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.verify', ['userId' => 'user1']), ['password' => $this->securePassword]);
        $response->assertOk();
        $response->assertJson(['message' => 'Authorized', 'needs_rehash' => true]);
    });

    it('fails to store insecure password', function () {
        $this->updateAuthorizationHeaders();

        $response = $this->postJson(route('vault.password.store', ['userId' => 'user1']), ['password' => 'insecure-password']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        expect(Hash::query()->count())->toBe(0);
    });
});
