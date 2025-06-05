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
    protected $signature = 'vault-server:install {--force}';

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
        $this->call('vendor:publish', [
            '--tag' => 'vault-migrations',
            '--force' => $this->option('force'),
        ]);

        $runMigrations = confirm(
            'Do you want to run the migrations now? (y/n)',
            true,
        );

        if ($runMigrations) {
            $this->call('migrate', [
                '--force' => $this->option('force'),
            ]);
        }

        $this->info('Installation completed successfully.');
    }
}
