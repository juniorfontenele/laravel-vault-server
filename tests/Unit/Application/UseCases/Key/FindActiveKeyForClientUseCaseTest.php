<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Application\UseCases\Key;

use DateTimeImmutable;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\KeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\FindActiveKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Key;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\KeyId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\ClientId;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects\PublicKey;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class FindActiveKeyForClientUseCaseTest extends TestCase
{
    private KeyRepositoryInterface|MockInterface $keyRepository;

    private FindActiveKeyForClientUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyRepository = Mockery::mock(KeyRepositoryInterface::class);
        $this->useCase = new FindActiveKeyForClientUseCase($this->keyRepository);
    }

    public function testExecuteWithExistingKey(): void
    {
        $clientId = 'test-client-id';
        $keyId = 'test-key-id';
        $publicKeyValue = 'public-key-content';
        $validFrom = new DateTimeImmutable();
        $validUntil = new DateTimeImmutable('+1 year');

        $keyIdMock = Mockery::mock(KeyId::class);
        $keyIdMock->shouldReceive('value')->andReturn($keyId);

        $clientIdMock = Mockery::mock(ClientId::class);
        $clientIdMock->shouldReceive('value')->andReturn($clientId);

        $publicKeyMock = Mockery::mock(PublicKey::class);
        $publicKeyMock->shouldReceive('value')->andReturn($publicKeyValue);

        $key = Mockery::mock(Key::class, [
            $keyIdMock,
            $clientIdMock,
            $publicKeyMock,
            1,
            $validFrom,
            $validUntil,
            false,
            null,
        ]);

        $key->shouldReceive('keyId')->andReturn($keyId);
        $key->shouldReceive('clientId')->andReturn($clientId);
        $key->shouldReceive('publicKey')->andReturn($publicKeyValue);
        $key->shouldReceive('version')->andReturn(1);
        $key->shouldReceive('validFrom')->andReturn($validFrom);
        $key->shouldReceive('validUntil')->andReturn($validUntil);
        $key->shouldReceive('isRevoked')->andReturn(false);
        $key->shouldReceive('revokedAt')->andReturn(null);

        $this->keyRepository
            ->shouldReceive('findActiveKeyByClientId')
            ->once()
            ->with($clientId)
            ->andReturn($key);

        $result = $this->useCase->execute($clientId);

        $this->assertInstanceOf(KeyResponseDTO::class, $result);
        $this->assertEquals($keyId, $result->keyId);
        $this->assertEquals($clientId, $result->clientId);
        $this->assertEquals($publicKeyValue, $result->publicKey);
        $this->assertEquals(1, $result->version);
        $this->assertSame($validFrom, $result->validFrom);
        $this->assertSame($validUntil, $result->validUntil);
        $this->assertFalse($result->isRevoked);
        $this->assertNull($result->revokedAt);
    }

    public function testExecuteWithNoKey(): void
    {
        $clientId = 'test-client-id';

        $this->keyRepository
            ->shouldReceive('findActiveKeyByClientId')
            ->once()
            ->with($clientId)
            ->andReturnNull();

        $result = $this->useCase->execute($clientId);

        $this->assertNull($result);
    }
}
