<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Key;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\RotateKeyUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\PublicKeyException;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class RotateKeyUseCaseTest extends TestCase
{
    private KeyRepositoryInterface|MockInterface $keyRepository;

    private UnitOfWorkInterface|MockInterface $unitOfWork;

    private RotateKeyUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyRepository = Mockery::mock(KeyRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->useCase = new RotateKeyUseCase($this->keyRepository, $this->unitOfWork);
    }

    public function testExecuteSuccess(): void
    {
        $keyId = (new KeyId())->value();
        $clientId = (new ClientId())->value();

        $key = Mockery::mock(Key::class);
        $key->shouldReceive('clientId')->andReturn($clientId);

        $this->keyRepository
            ->shouldReceive('findKeyByKeyId')
            ->once()
            ->with($keyId)
            ->andReturn($key);

        $nonRevokedKey1 = Mockery::mock(Key::class);
        $nonRevokedKey1->shouldReceive('revoke')->once()->andReturnNull();

        $nonRevokedKey2 = Mockery::mock(Key::class);
        $nonRevokedKey2->shouldReceive('revoke')->once()->andReturnNull();

        $this->keyRepository
            ->shouldReceive('findAllNonRevokedKeysByClientId')
            ->once()
            ->with($clientId)
            ->andReturn([$nonRevokedKey1, $nonRevokedKey2]);

        $this->keyRepository
            ->shouldReceive('save')
            ->times(3) // 2 revoked keys + 1 new key
            ->andReturnNull();

        $this->keyRepository
            ->shouldReceive('maxVersion')
            ->once()
            ->with($clientId)
            ->andReturn(2);

        $this->unitOfWork
            ->shouldReceive('execute')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->useCase->execute($keyId);

        $this->assertInstanceOf(CreateKeyResponseDTO::class, $result);
        $this->assertNotEmpty($result->keyId);
        $this->assertEquals($clientId, $result->clientId);
        $this->assertNotEmpty($result->publicKey);
        $this->assertNotEmpty($result->privateKey);
        $this->assertEquals(3, $result->version); // maxVersion + 1
        $this->assertInstanceOf(DateTimeImmutable::class, $result->validFrom);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->validUntil);
    }

    public function testExecuteWithNonExistingKeyThrowsException(): void
    {
        $keyId = 'non-existing-key-id';

        $this->keyRepository
            ->shouldReceive('findKeyByKeyId')
            ->once()
            ->with($keyId)
            ->andReturnNull();

        $this->expectException(PublicKeyException::class);

        $this->useCase->execute($keyId);
    }
}
