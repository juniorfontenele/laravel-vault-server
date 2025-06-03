<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Artifacts\NewClient;
use JuniorFontenele\LaravelVaultServer\Artifacts\NewKey;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientCreated;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientDeleted;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientProvisioned;
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientTokenGenerated;
use JuniorFontenele\LaravelVaultServer\Events\Client\InactiveClientsCleanup;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientAlreadyProvisionedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotAuthenticatedException;
use JuniorFontenele\LaravelVaultServer\Exceptions\Client\ClientNotFoundException;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Services\ClientManagerService;

beforeEach(function () {
    // Clean up the Client table before each test
    Client::query()->delete();
});

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

describe('ClientManagerService', function () {
    it('creates a client', function () {
        Event::fake();
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        expect($client)->toBeInstanceOf(NewClient::class);
        expect($client->client->name)->toBe('Test');
        Event::assertDispatched(ClientCreated::class);
    });

    it('provisions a client', function () {
        Event::fake();
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        // Corrige para usar o nome correto do atributo
        $provisionToken = $client->plaintext_provision_token;
        // Simula hash correto usando Hash::make
        $client->client->provision_token = Illuminate\Support\Facades\Hash::make($provisionToken);
        $client->client->save();
        $newKey = $service->provisionClient($client->client->id, $provisionToken);
        expect($newKey)->toBeInstanceOf(NewKey::class);
        Event::assertDispatched(ClientProvisioned::class);
    });

    it('throws ClientNotFoundException on provision with invalid id', function () {
        $service = app(ClientManagerService::class);
        $this->expectException(ClientNotFoundException::class);
        $service->provisionClient('invalid', 'token');
    });

    it('throws ClientAlreadyProvisionedException if already provisioned', function () {
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        $client->client->provision_token = null;
        $client->client->save();
        $this->expectException(ClientAlreadyProvisionedException::class);
        $service->provisionClient($client->client->id, 'token');
    });

    it('throws ClientNotAuthenticatedException if provision token is invalid', function () {
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        // Simula hash correto usando Hash::make
        $client->client->provision_token = Illuminate\Support\Facades\Hash::make('right');
        $client->client->save();
        $this->expectException(ClientNotAuthenticatedException::class);
        $service->provisionClient($client->client->id, 'wrong');
    });

    it('reprovisions a client', function () {
        Event::fake();
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        $newClient = $service->reprovisionClient($client->client->id);
        expect($newClient)->toBeInstanceOf(NewClient::class);
        Event::assertDispatched(ClientTokenGenerated::class);
    });

    it('throws ClientNotFoundException on reprovision with invalid id', function () {
        $service = app(ClientManagerService::class);
        $this->expectException(ClientNotFoundException::class);
        $service->reprovisionClient('invalid');
    });

    it('deletes a client', function () {
        Event::fake();
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        $service->deleteClient($client->client->id);
        expect(Client::find($client->client->id))->toBeNull();
        Event::assertDispatched(ClientDeleted::class);
    });

    it('throws ClientNotFoundException on delete with invalid id', function () {
        $service = app(ClientManagerService::class);
        $this->expectException(ClientNotFoundException::class);
        $service->deleteClient('invalid');
    });

    it('cleans up inactive clients', function () {
        Event::fake();
        $service = app(ClientManagerService::class);
        $client = $service->createClient('Test', [Scope::KEYS_READ->value], 'desc');
        $client->client->is_active = false;
        $client->client->save();
        $deleted = $service->cleanupInactiveClients();
        expect($deleted)->toContain($client->client->id);
        Event::assertDispatched(InactiveClientsCleanup::class);
    });

    it('returns all clients', function () {
        $service = app(ClientManagerService::class);
        $service->createClient('Test1', [Scope::KEYS_READ->value], 'desc');
        $service->createClient('Test2', [Scope::KEYS_READ->value], 'desc');
        $all = $service->all();
        expect($all->count())->toBeGreaterThanOrEqual(2);
    });
});
