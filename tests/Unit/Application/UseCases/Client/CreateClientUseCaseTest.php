<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Client\CreateClientResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\CreateClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class CreateClientUseCaseTest extends TestCase
{
    private ClientRepositoryInterface|MockInterface $clientRepository;

    private CreateClientUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new CreateClientUseCase($this->clientRepository);
    }

    public function testExecute(): void
    {
        $this->clientRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Client::class))
            ->andReturnNull();

        $dto = new CreateClientDTO(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'hashes:read'],
            description: 'Test description'
        );

        $response = $this->useCase->execute($dto);

        $this->assertInstanceOf(CreateClientResponseDTO::class, $response);
        $this->assertEquals('Test Client', $response->name);
        $this->assertEquals(['keys:read', 'hashes:read'], $response->allowedScopes);
        $this->assertEquals('Test description', $response->description);
        $this->assertNotEmpty($response->clientId);
        $this->assertNotEmpty($response->provisionToken);
    }
}
