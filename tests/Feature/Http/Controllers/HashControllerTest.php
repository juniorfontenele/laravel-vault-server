<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature\Http\Controllers;

use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Ramsey\Uuid\Uuid;

class HashControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadWorkbenchMigrations = true;

        $this->updateAuthorizationHeaders();
    }

    public function testStoreHash(): void
    {
        $userId = Uuid::uuid4()->toString();
        $hash = 'hash-value-for-testing';

        $response = $this->postJson(route('vault.hash.store', [$userId]), [
            'hash' => $hash,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'hash_id',
                'user_id',
                'hash',
                'version',
                'is_revoked',
            ])
            ->assertJson([
                'user_id' => $userId,
                'hash' => $hash,
                'version' => 1,
                'is_revoked' => false,
            ]);
    }

    public function testShowHash(): void
    {
        $userId = Uuid::uuid4()->toString();
        $hash = 'hash-value-for-testing';

        // Store hash first
        $this->postJson(route('vault.hash.store', [$userId]), [
            'hash' => $hash,
        ]);

        $this->updateAuthorizationHeaders();

        $response = $this->getJson(route('vault.hash.get', [$userId]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'hash_id',
                'user_id',
                'hash',
                'version',
                'is_revoked',
            ])
            ->assertJson([
                'user_id' => $userId,
                'hash' => $hash,
                'version' => 1,
                'is_revoked' => false,
            ]);
    }

    public function testShowHashWithNonExistingUser(): void
    {
        $userId = Uuid::uuid4()->toString();

        $response = $this->getJson(route('vault.hash.get', [$userId]));

        $response->assertStatus(404)
            ->assertJsonStructure(['error']);
    }

    public function testDeleteHash(): void
    {
        $userId = Uuid::uuid4()->toString();
        $hash = 'hash-value-for-testing';

        // Store hash first
        $this->postJson(route('vault.hash.store', [$userId]), [
            'hash' => $hash,
        ]);

        $this->updateAuthorizationHeaders();

        $response = $this->deleteJson(route('vault.hash.destroy', [$userId]));

        $response->assertStatus(204);

        $this->updateAuthorizationHeaders();

        // Verify hash was deleted
        $checkResponse = $this->getJson(route('vault.hash.get', [$userId]));
        $checkResponse->assertStatus(404);
    }

    public function testStoreHashWithoutHash(): void
    {
        $userId = Uuid::uuid4()->toString();

        $response = $this->postJson(route('vault.hash.store', [$userId]), []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    public function testStoreHashWithInvalidHash(): void
    {
        $userId = Uuid::uuid4()->toString();

        // Create a string longer than 255 characters
        $hash = str_repeat('a', 300);

        $response = $this->postJson(route('vault.hash.store', [$userId]), [
            'hash' => $hash,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }
}
