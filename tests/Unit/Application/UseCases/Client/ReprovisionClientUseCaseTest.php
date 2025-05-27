<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ReprovisionClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class ReprovisionClientUseCaseTest extends TestCase
{
    private ClientRepositoryInterface|MockInterface $clientRepository;

    private ReprovisionClientUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new ReprovisionClientUseCase($this->clientRepository);
    }

    public function testExecuteSuccess(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('clientId')->andReturn('test-client-id');
        $client->shouldReceive('name')->andReturn('Test Client');
        $client->shouldReceive('scopes')->andReturn(['keys:read', 'hashes:read']);
        $client->shouldReceive('provisionToken')->andReturn('new-token');
        $client->shouldReceive('description')->andReturn('Test description');
        $client->shouldReceive('reprovision')->once()->andReturnNull();

        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('test-client-id')
            ->andReturn($client);

        $this->clientRepository
            ->shouldReceive('save')
            ->once()
            ->with($client)
            ->andReturnNull();

        $response = $this->useCase->execute('test-client-id');

        $this->assertInstanceOf(CreateClientResponseDTO::class, $response);
        $this->assertEquals('test-client-id', $response->clientId);
        $this->assertEquals('Test Client', $response->name);
        $this->assertEquals(['keys:read', 'hashes:read'], $response->allowedScopes);
        $this->assertEquals('new-token', $response->provisionToken);
        $this->assertEquals('Test description', $response->description);
    }

    public function testExecuteWithNonExistingClientThrowsException(): void
    {
        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('non-existing-id')
            ->andReturnNull();

        $this->expectException(ClientException::class);

        $this->useCase->execute('non-existing-id');
    }
}
