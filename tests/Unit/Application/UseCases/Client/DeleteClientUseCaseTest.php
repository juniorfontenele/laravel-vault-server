<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\DeleteClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class DeleteClientUseCaseTest extends TestCase
{
    private ClientRepositoryInterface|MockInterface $clientRepository;

    private DeleteClientUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new DeleteClientUseCase($this->clientRepository);
    }

    public function testExecuteSuccess(): void
    {
        $client = Mockery::mock(Client::class);

        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('test-client-id')
            ->andReturn($client);

        $this->clientRepository
            ->shouldReceive('delete')
            ->once()
            ->with($client)
            ->andReturnNull();

        $this->useCase->execute('test-client-id');

        // If no exception is thrown, the test is successful
        $this->assertTrue(true);
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
