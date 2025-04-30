<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Eloquent\EloquentClientRepository;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\ClientModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\HashModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\KeyModel;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Services\KeyPairService;
use JuniorFontenele\LaravelVaultServer\Interfaces\Console\Commands\VaultClientManagement;
use JuniorFontenele\LaravelVaultServer\Interfaces\Console\Commands\VaultInstallCommand;
use JuniorFontenele\LaravelVaultServer\Interfaces\Console\Commands\VaultKeyManager;
use JuniorFontenele\LaravelVaultServer\Interfaces\Http\Middlewares\ValidateJwtToken;

class LaravelVaultServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../../routes/vault.php');

        $this->publishes([
            __DIR__ . '/../../../../routes/vault.php' => base_path('routes/vault.php'),
        ], 'routes');

        $this->loadMigrationsFrom(__DIR__ . '/../../../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../../../config/vault.php' => config_path('vault.php'),
        ], 'config');

        $this->app->singleton(KeyPairService::class, function ($app) {
            return new KeyPairService();
        });

        $loader = AliasLoader::getInstance();
        $loader->alias('VaultKey', VaultKey::class);
        $loader->alias('VaultClientManager', VaultClientManager::class);
        $loader->alias('VaultJWT', VaultJWT::class);

        /** @var Router $router */
        $router = app('router');
        $router->aliasMiddleware('vault.jwt', ValidateJwtToken::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        ClientModel::unguard();
        HashModel::unguard();
        KeyModel::unguard();

        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);

        $this->mergeConfigFrom(__DIR__ . '/../../../../config/vault.php', 'vault');

        if ($this->app->runningInConsole()) {
            $this->commands([
                VaultKeyManager::class,
                VaultClientManagement::class,
                VaultInstallCommand::class,
            ]);
        }
    }
}
