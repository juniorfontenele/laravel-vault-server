<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ProvisionClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CreateKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class ProvisionClientUseCaseTest extends TestCase
{
    private ClientRepositoryInterface|MockInterface $clientRepository;

    private UnitOfWorkInterface|MockInterface $unitOfWork;

    private ProvisionClientUseCase $useCase;

    private CreateKeyForClientUseCase|MockInterface $createKeyForClientUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);
        $this->createKeyForClientUseCase = Mockery::mock(CreateKeyForClientUseCase::class);

        // We need to bind our mock to the container for app() calls
        $this->app->instance(CreateKeyForClientUseCase::class, $this->createKeyForClientUseCase);

        $this->useCase = new ProvisionClientUseCase($this->clientRepository, $this->unitOfWork);
    }

    public function testExecuteSuccess(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('isProvisioned')->andReturn(false);
        $client->shouldReceive('clientId')->andReturn('test-client-id');
        $client->shouldReceive('verifyProvisionToken')->with('token')->andReturn(true);
        $client->shouldReceive('provision')->with('token')->andReturnNull();

        $keyResponse = Mockery::mock(CreateKeyResponseDTO::class);

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

        $this->createKeyForClientUseCase
            ->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(CreateKeyDTO::class))
            ->andReturn($keyResponse);

        $this->unitOfWork
            ->shouldReceive('execute')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->useCase->execute('test-client-id', 'token');

        $this->assertSame($keyResponse, $result);
    }

    public function testExecuteWithNonExistingClientThrowsException(): void
    {
        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('non-existing-id')
            ->andReturnNull();

        $this->expectException(ClientException::class);

        $this->useCase->execute('non-existing-id', 'token');
    }

    public function testExecuteWithAlreadyProvisionedClientThrowsException(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('isProvisioned')->andReturn(true);
        $client->shouldReceive('clientId')->andReturn('test-client-id');

        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('test-client-id')
            ->andReturn($client);

        $this->expectException(ClientException::class);

        $this->useCase->execute('test-client-id', 'token');
    }

    public function testExecuteWithInvalidTokenThrowsException(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('isProvisioned')->andReturn(false);
        $client->shouldReceive('clientId')->andReturn('test-client-id');
        $client->shouldReceive('verifyProvisionToken')->with('invalid-token')->andReturn(false);

        $this->clientRepository
            ->shouldReceive('findClientByClientId')
            ->once()
            ->with('test-client-id')
            ->andReturn($client);

        $this->expectException(ClientException::class);

        $this->useCase->execute('test-client-id', 'invalid-token');
    }
}
