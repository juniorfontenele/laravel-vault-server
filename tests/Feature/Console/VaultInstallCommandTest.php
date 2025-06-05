<?php

declare(strict_types = 1);

describe('VaultInstallCommand', function () {
    it('publishes migrations and runs them when confirmed', function () {
        $this->artisan('vault-server:install')
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', true)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('publishes migrations but skips running them when declined', function () {
        $this->artisan('vault-server:install')
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', false)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('can use force option to override existing files', function () {
        $this->artisan('vault-server:install', ['--force' => true])
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', true)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('accepts force option without errors', function () {
        $this->artisan('vault-server:install', ['--force' => true])
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', false)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('handles missing force option gracefully', function () {
        $this->artisan('vault-server:install')
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', false)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('shows success message regardless of migration choice', function () {
        $this->artisan('vault-server:install')
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', true)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);

        $this->artisan('vault-server:install')
            ->expectsQuestion('Do you want to run the migrations now? (y/n)', false)
            ->expectsOutput('Installation completed successfully.')
            ->assertExitCode(0);
    });

    it('has correct command signature', function () {
        $command = app(JuniorFontenele\LaravelVaultServer\Console\Commands\VaultInstallCommand::class);

        expect($command->getName())->toBe('vault-server:install');
        expect($command->getDescription())->toBe('Install the Vault module');
    });
});
