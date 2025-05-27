<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Feature;

use Illuminate\Routing\Router;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Contracts\HashRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Contracts\KeyRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Interfaces\Http\Middlewares\ValidateJwtToken;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent\EloquentClientRepository;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent\EloquentHashRepository;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Eloquent\EloquentKeyRepository;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\LaravelUnitOfWork;
use JuniorFontenele\LaravelVaultServer\Services\ClientManagerService;
use JuniorFontenele\LaravelVaultServer\Services\JwtService;
use JuniorFontenele\LaravelVaultServer\Services\KeyPairService;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class LaravelVaultServerServiceProviderTest extends TestCase
{
    public function testServiceProviderBindings(): void
    {
        $this->assertTrue($this->app->bound(ClientRepositoryInterface::class));
        $this->assertTrue($this->app->bound(KeyRepositoryInterface::class));
        $this->assertTrue($this->app->bound(HashRepositoryInterface::class));
        $this->assertTrue($this->app->bound(UnitOfWorkInterface::class));

        $this->assertInstanceOf(
            EloquentClientRepository::class,
            $this->app->make(ClientRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            EloquentKeyRepository::class,
            $this->app->make(KeyRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            EloquentHashRepository::class,
            $this->app->make(HashRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            LaravelUnitOfWork::class,
            $this->app->make(UnitOfWorkInterface::class)
        );
    }

    public function testFacadesAreRegistered(): void
    {
        $this->assertTrue(class_exists('VaultKey'));
        $this->assertTrue(class_exists('VaultClientManager'));
        $this->assertTrue(class_exists('VaultJWT'));

        $this->assertEquals(KeyPairService::class, get_class(VaultKey::getFacadeRoot()));
        $this->assertEquals(ClientManagerService::class, get_class(VaultClientManager::getFacadeRoot()));
        $this->assertEquals(JwtService::class, get_class(VaultJWT::getFacadeRoot()));
    }

    public function testMiddlewareRegistration(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $middlewareAliases = $router->getMiddleware();

        $this->assertArrayHasKey('vault.jwt', $middlewareAliases);
        $this->assertEquals(ValidateJwtToken::class, $middlewareAliases['vault.jwt']);
    }

    public function testConfigIsLoaded(): void
    {
        $this->assertTrue($this->app['config']->has('vault'));
    }
}
