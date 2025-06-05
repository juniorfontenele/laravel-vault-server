<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Console\Commands;

use Illuminate\Console\Command;
use JuniorFontenele\LaravelSecureJwt\Facades\SecureJwt;
use JuniorFontenele\LaravelSecureJwt\JwtKey;

class PlayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        SecureJwt::decode('your.jwt.token.here', new JwtKey(
            'your-key-id',
            'your-key-value',
            'HS256'
        ));
    }
}
