<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class VaultInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vault:install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Vault module';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $enableServer = confirm(
            'Do you want to enable the Vault Server? (y/n)',
            false,
        );

        $this->generateConfigFile($enableServer);

        $publishConfig = confirm(
            'Do you want to publish the configuration files? (y/n)',
            true,
        );

        if ($publishConfig) {
            $this->call('vendor:publish', [
                '--tag' => 'config',
                '--provider' => 'JuniorFontenele\LaravelVaultServer\Providers\LaravelVaultServiceProvider',
                '--force' => (bool) $this->option('force'),
            ]);
        }

        $runMigrations = confirm(
            'Do you want to run the migrations now? (y/n)',
            true,
        );

        if ($runMigrations) {
            $this->call('migrate', [
                '--realpath' => __DIR__ . '/../../../database/migrations',
            ]);
        }

        $this->info('Installation completed successfully.');
    }

    protected function generateConfigFile(bool $serverEnabled): void
    {
        $configFile = __DIR__ . '/../../../config/vault.php';

        $configContent = file_get_contents($configFile);
        $configContent = str_replace(
            "'server_enabled' => true,",
            "'server_enabled' => " . ($serverEnabled ? 'true' : 'false') . ",",
            $configContent
        );

        file_put_contents($configFile, $configContent);
    }
}
