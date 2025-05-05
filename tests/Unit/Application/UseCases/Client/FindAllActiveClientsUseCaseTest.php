<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\ClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\FindAllActiveClientsUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class FindAllActiveClientsUseCaseTest extends TestCase
{
    private ClientRepositoryInterface|MockInterface $clientRepository;

    private FindAllActiveClientsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new FindAllActiveClientsUseCase($this->clientRepository);
    }

    public function testExecute(): void
    {
        $client1 = Mockery::mock(Client::class);
        $client1->shouldReceive('clientId')->andReturn('client-id-1');
        $client1->shouldReceive('name')->andReturn('Client 1');
        $client1->shouldReceive('scopes')->andReturn(['keys:read']);
        $client1->shouldReceive('description')->andReturn('Description 1');

        $client2 = Mockery::mock(Client::class);
        $client2->shouldReceive('clientId')->andReturn('client-id-2');
        $client2->shouldReceive('name')->andReturn('Client 2');
        $client2->shouldReceive('scopes')->andReturn(['keys:read', 'hashes:read']);
        $client2->shouldReceive('description')->andReturn('Description 2');

        $activeClients = [$client1, $client2];

        $this->clientRepository
            ->shouldReceive('findAllActiveClients')
            ->once()
            ->andReturn($activeClients);

        $result = $this->useCase->execute();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ClientResponseDTO::class, $result);
        $this->assertEquals('client-id-1', $result[0]->clientId);
        $this->assertEquals('Client 1', $result[0]->name);
        $this->assertEquals(['keys:read'], $result[0]->allowedScopes);
        $this->assertEquals('Description 1', $result[0]->description);

        $this->assertEquals('client-id-2', $result[1]->clientId);
        $this->assertEquals('Client 2', $result[1]->name);
        $this->assertEquals(['keys:read', 'hashes:read'], $result[1]->allowedScopes);
        $this->assertEquals('Description 2', $result[1]->description);
    }
}
