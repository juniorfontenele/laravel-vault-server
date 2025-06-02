<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use JuniorFontenele\LaravelSecureJwt\Contracts\JwtBlacklistRepositoryInterface;
use JuniorFontenele\LaravelSecureJwt\Contracts\JwtClaimValidatorInterface;
use JuniorFontenele\LaravelSecureJwt\Contracts\JwtDriverInterface;
use JuniorFontenele\LaravelSecureJwt\Contracts\JwtNonceRepositoryInterface;
use JuniorFontenele\LaravelSecureJwt\JwtConfig;
use JuniorFontenele\LaravelVaultServer\Console\Commands\Play;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultClientManagement;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultInstallCommand;
use JuniorFontenele\LaravelVaultServer\Console\Commands\VaultKeyManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Facades\VaultJWT;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Http\Middlewares\ValidateJwtToken;

class LaravelVaultServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setupBindings();

        $this->setupRoutes();

        $this->setupPublications();

        $this->setupAliases();

        $this->setupMiddlewares();

        $this->setupCommands();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    private function setupBindings(): void
    {
        $this->app->singleton(JwtConfig::class, function (): JwtConfig {
            return new JwtConfig(
                issuer: config('vault.jwt.issuer'),
                ttl: config('vault.jwt.ttl'),
                nonceTtl: config('vault.jwt.nonce_ttl'),
                blacklistTtl: config('vault.jwt.blacklist_ttl'),
            );
        });

        $this->app->singleton(JwtDriverInterface::class, config('vault.providers.jwt_driver'));
        $this->app->singleton(JwtBlacklistRepositoryInterface::class, config('vault.providers.jwt_blacklist'));
        $this->app->singleton(JwtNonceRepositoryInterface::class, config('vault.providers.jwt_nonce'));
        $this->app->singleton(JwtClaimValidatorInterface::class, config('vault.providers.jwt_claim_validator'));
    }

    private function setupAliases(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('VaultKey', VaultKey::class);
        $loader->alias('VaultClientManager', VaultClientManager::class);
        $loader->alias('VaultJWT', VaultJWT::class);
    }

    private function setupMiddlewares(): void
    {
        /** @var Router $router */
        $router = app('router');
        $router->aliasMiddleware('vault.jwt', ValidateJwtToken::class);
    }

    private function setupPublications(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/vault.php' => config_path('vault.php'),
        ], 'vault-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'vault-migrations');

        $this->publishes([
            __DIR__ . '/../../routes/vault.php' => base_path('routes/vault.php'),
        ], 'vault-routes');
    }

    private function setupRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/vault.php');
    }

    private function setupCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                VaultKeyManager::class,
                VaultClientManagement::class,
                VaultInstallCommand::class,
                Play::class,
            ]);
        }
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/vault.php', 'vault');
    }
}
