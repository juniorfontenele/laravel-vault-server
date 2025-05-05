<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Key;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CreateKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindAllKeysForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\RevokeKeyUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class CreateKeyForClientUseCaseTest extends TestCase
{
    private KeyRepositoryInterface|MockInterface $keyRepository;

    private UnitOfWorkInterface|MockInterface $unitOfWork;

    private FindAllKeysForClientUseCase|MockInterface $findAllKeysForClientUseCase;

    private RevokeKeyUseCase|MockInterface $revokeKeyUseCase;

    private CreateKeyForClientUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyRepository = Mockery::mock(KeyRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);
        $this->findAllKeysForClientUseCase = Mockery::mock(FindAllKeysForClientUseCase::class);
        $this->revokeKeyUseCase = Mockery::mock(RevokeKeyUseCase::class);

        $this->useCase = new CreateKeyForClientUseCase(
            $this->keyRepository,
            $this->unitOfWork,
            $this->findAllKeysForClientUseCase,
            $this->revokeKeyUseCase
        );
    }

    public function testExecuteSuccess(): void
    {
        $clientId = (new ClientId())->value();
        $keyDto = new CreateKeyDTO(
            clientId: $clientId,
            keySize: 2048,
            expiresIn: 365
        );

        // Create KeyResponseDTO objects with the correct keyId values
        $keyId1 = (new KeyId())->value();
        $keyId2 = (new KeyId())->value();

        $existingKey1 = new KeyResponseDTO(
            keyId: $keyId1,
            clientId: $clientId,
            publicKey: 'public-key-1',
            version: 1,
            validFrom: new DateTimeImmutable(),
            validUntil: new DateTimeImmutable('+1 year'),
            isRevoked: false,
            revokedAt: null
        );

        $existingKey2 = new KeyResponseDTO(
            keyId: $keyId2,
            clientId: $clientId,
            publicKey: 'public-key-2',
            version: 2,
            validFrom: new DateTimeImmutable(),
            validUntil: new DateTimeImmutable('+1 year'),
            isRevoked: false,
            revokedAt: null
        );

        $this->findAllKeysForClientUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($clientId)
            ->andReturn([$existingKey1, $existingKey2]);

        // Use the same keyId values that match the KeyResponseDTO objects
        $this->revokeKeyUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($keyId1)
            ->andReturnNull();

        $this->revokeKeyUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($keyId2)
            ->andReturnNull();

        $this->keyRepository
            ->shouldReceive('maxVersion')
            ->once()
            ->with($clientId)
            ->andReturn(2);

        $this->keyRepository
            ->shouldReceive('save')
            ->once()
            ->andReturnNull();

        $this->unitOfWork
            ->shouldReceive('execute')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->useCase->execute($keyDto);

        $this->assertInstanceOf(CreateKeyResponseDTO::class, $result);
        $this->assertNotEmpty($result->keyId);
        $this->assertEquals($clientId, $result->clientId);
        $this->assertNotEmpty($result->publicKey);
        $this->assertNotEmpty($result->privateKey);
        $this->assertEquals(3, $result->version); // Should be maxVersion + 1
        $this->assertInstanceOf(DateTimeImmutable::class, $result->validFrom);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->validUntil);
    }
}
