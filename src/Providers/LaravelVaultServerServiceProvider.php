<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultClientManagement;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultInstallCommand;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultKeyManager;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultKeyRotateCommand;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultProvisionClientCommand;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Http\Middlewares\ValidateJwtToken;
use JuniorFontenele\LaravelVaultServer\Services\KeyPairService;
use JuniorFontenele\LaravelVaultServer\Services\VaultClientService;

class LaravelVaultServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('vault.server_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/vault.php');

            $this->publishes([
                __DIR__ . '/../../routes/vault.php' => base_path('routes/vault.php'),
            ], 'routes');
        }

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
        $loader->alias('VaultClient', VaultClientService::class);

        /** @var Router $router */
        $router = app('router');
        $router->aliasMiddleware('vault.jwt', ValidateJwtToken::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/vault.php', 'vault');

        if ($this->app->runningInConsole()) {
            if (config('vault.server_enabled', true)) {
                $this->commands([
                    VaultKeyManager::class,
                    VaultClientManagement::class,
                ]);
            }

            $this->commands([
                VaultInstallCommand::class,
                VaultProvisionClientCommand::class,
                VaultKeyRotateCommand::class,
            ]);
        }
    }
}
