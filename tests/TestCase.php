<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests;

use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Client\ProvisionClientUseCase;
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ramsey\Uuid\Uuid;

class TestCase extends OrchestraTestCase
{
    protected $enablesPackageDiscoveries = true;

    protected bool $loadWorkbenchMigrations = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        // Always run package migrations for feature tests
        if (str_contains(get_class($this), 'Feature')) {
            $this->loadVaultMigrations();
        }

        // For tests that explicitly set this flag
        if ($this->loadWorkbenchMigrations) {
            $this->loadVaultMigrations();
        }
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \JuniorFontenele\LaravelVaultServer\Providers\LaravelVaultServerServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app)
    {
        // Setup environment, like app configuration
        tap($app['config'], function (Repository $config) {
            $config->set('app.timezone', 'UTC');
            $config->set('app.locale', 'en');
            $config->set('app.fallback_locale', 'en');

            $config->set('database.default', 'sqlite');
            $config->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            // Configure the vault table prefix
            $config->set('vault.migrations.table_prefix', 'vault_');
        });

        Date::use(CarbonImmutable::class);
    }

    /**
     * Set up the database.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        // Create basic tables needed for tests
        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });
    }

    /**
     * Load vault migrations manually
     */
    protected function loadVaultMigrations(): void
    {
        // Find all migration files in the package's migrations directory
        $migrationPath = realpath(dirname(__DIR__) . '/database/migrations');

        if ($migrationPath) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function getLaravelVersion()
    {
        return (float) app()->version();
    }

    protected function getJwtToken(): string
    {
        $client = VaultClientManager::createClient(
            name: 'Test Client',
            allowedScopes: ['keys:read', 'keys:rotate', 'keys:delete', 'hashes:read', 'hashes:create', 'hashes:delete'],
        );

        $createKeyResponseDTO = app(ProvisionClientUseCase::class)->execute(
            clientId: $client->clientId,
            provisionToken: $client->provisionToken,
        );

        $jwtPayload = [
            'jti' => Uuid::uuid7()->toString(),
            'nonce' => bin2hex(random_bytes(16)),
            'iss' => 'testing',
            'iat' => time(),
            'exp' => time() + now()->addMinutes(5)->timestamp,
            'client_id' => $client->clientId,
            'scopes' => $client->allowedScopes,
            'kid' => $createKeyResponseDTO->keyId,
        ];

        $token = JWT::encode(
            $jwtPayload,
            $createKeyResponseDTO->privateKey,
            'RS256',
            $createKeyResponseDTO->keyId
        );

        return $token;
    }

    protected function updateAuthorizationHeaders()
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->getJwtToken(),
            'Accept' => 'application/json',
        ]);
    }
}
