<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultClientManagement;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultInstallCommand;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultKeyManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Http\Middlewares\ValidateJwtToken;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Services\KeyPairService;

class LaravelVaultServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/vault.php');

        $this->publishes([
            __DIR__ . '/../../routes/vault.php' => base_path('routes/vault.php'),
        ], 'routes');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../config/vault.php' => config_path('vault.php'),
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
        Client::unguard();
        Hash::unguard();
        Key::unguard();

        $this->mergeConfigFrom(__DIR__ . '/../../config/vault.php', 'vault');

        if ($this->app->runningInConsole()) {
            $this->commands([
                VaultKeyManager::class,
                VaultClientManagement::class,
                VaultInstallCommand::class,
            ]);
        }
    }
}
