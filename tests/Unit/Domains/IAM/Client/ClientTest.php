<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\AllowedScopes;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\ProvisionToken;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ClientTest extends TestCase
{
    private Client $client;

    private ClientId $clientId;

    private AllowedScopes $allowedScopes;

    private ProvisionToken $provisionToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientId = new ClientId();
        $this->allowedScopes = new AllowedScopes([Scope::KEYS_READ, Scope::PASSWORDS_VERIFY]);
        $this->provisionToken = new ProvisionToken();

        $this->client = new Client(
            $this->clientId,
            'Test Client',
            $this->allowedScopes,
            true,
            'Test Description',
            $this->provisionToken
        );
    }

    public function testClientCreation(): void
    {
        $this->assertEquals($this->clientId->value(), $this->client->clientId());
        $this->assertEquals('Test Client', $this->client->name());
        $this->assertEquals('Test Description', $this->client->description());
        $this->assertCount(2, $this->client->scopes());
        $this->assertTrue($this->client->isActive());
        $this->assertFalse($this->client->isProvisioned());
        $this->assertTrue($this->client->isNotProvisioned());
        $this->assertNull($this->client->provisionedAt());
        $this->assertEquals($this->provisionToken->plainValue(), $this->client->provisionToken());
    }

    public function testProvisionClient(): void
    {
        $tokenValue = $this->provisionToken->plainValue();
        $this->client->provision($tokenValue);

        $this->assertTrue($this->client->isProvisioned());
        $this->assertFalse($this->client->isNotProvisioned());
        $this->assertNull($this->client->provisionToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->client->provisionedAt());
    }

    public function testProvisionClientWithInvalidTokenThrowsException(): void
    {
        $this->expectException(ClientException::class);

        $this->client->provision('invalid-token');
    }

    public function testProvisionClientThatIsAlreadyProvisionedThrowsException(): void
    {
        $this->client->provision($this->provisionToken->plainValue());

        $this->expectException(ClientException::class);

        $this->client->provision($this->provisionToken->plainValue());
    }

    public function testReprovisionClient(): void
    {
        $this->client->provision($this->provisionToken->plainValue());
        $this->assertTrue($this->client->isProvisioned());

        $this->client->reprovision();

        $this->assertFalse($this->client->isProvisioned());
        $this->assertNotNull($this->client->provisionToken());
    }

    public function testDeactivateClient(): void
    {
        $this->assertTrue($this->client->isActive());

        $this->client->deactivate();

        $this->assertFalse($this->client->isActive());
    }

    public function testActivateClient(): void
    {
        $this->client->deactivate();
        $this->assertFalse($this->client->isActive());

        $this->client->activate();

        $this->assertTrue($this->client->isActive());
    }
}
