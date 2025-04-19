<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelVaultServerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(
        //     __DIR__.'/../../config/config.php',
        //     'skeleton'
        // );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->publishes([
        //     __DIR__.'/../../config/config.php' => config_path('skeleton.php'),
        // ], 'config');
    }
}
