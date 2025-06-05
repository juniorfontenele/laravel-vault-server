<?php

declare(strict_types = 1);

describe('Architecture Tests', function () {
    // describe('Debugging Functions', function () {
    //     it('will not use debugging functions')
    //         ->expect(['dd', 'dump', 'ray', 'ds'])
    //         ->not->toBeUsed();
    // });

    describe('Structure', function () {
        it('ensures Models are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Models')
            ->toBeClasses()
            ->classes()
            ->toExtend('Illuminate\Database\Eloquent\Model')
            ->not->toUse([
                'Illuminate\Support\Facades\DB',
                'Illuminate\Support\Facades\Cache',
                'Illuminate\Support\Facades\Http',
                'Illuminate\Support\Facades\Mail',
                'Illuminate\Support\Facades\Queue',
            ]);

        it('ensures Services are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Services')
            ->toBeClasses()
            ->classes()
            ->toExtendNothing()
            ->toHaveSuffix('Service');

        it('ensures Controllers are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Http\Controllers')
            ->toBeClasses()
            ->classes()
            ->toExtend('Illuminate\Routing\Controller')
            ->toHaveSuffix('Controller');

        it('ensures Exceptions are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Exceptions')
            ->toBeClasses()
            ->classes()
            ->toExtend('Exception')
            ->toHaveSuffix('Exception');

        it('ensures Events are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Events')
            ->toBeClasses()
            ->classes()
            ->toExtendNothing()
            ->toHaveConstructor()
            ->toUseTraits([
                'Illuminate\Broadcasting\InteractsWithSockets',
                'Illuminate\Queue\SerializesModels',
                'Illuminate\Foundation\Events\Dispatchable',
            ]);

        it('ensures Enums are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Enums')
            ->toBeEnums()
            ->enums()
            ->toBeStringBackedEnums();

        it('ensures Contracts are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Contracts')
            ->toBeInterfaces();

        it('ensures Queries are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Queries')
            ->toBeClasses()
            ->classes()
            ->toHaveSuffix('QueryBuilder')
            ->toExtend('JuniorFontenele\LaravelVaultServer\Queries\AbstractQueryBuilder')
            ->toHaveConstructor();

        it('ensures Query Filters are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Filters')
            ->toBeClasses()
            ->classes()
            ->toHaveSuffix('Filter')
            ->toImplement('JuniorFontenele\LaravelVaultServer\Contracts\QueryFilterInterface');

        it('ensures Facades are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Facades')
            ->toBeClasses()
            ->classes()
            ->toExtend('Illuminate\Support\Facades\Facade');

        it('ensures Data classes are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Data')
            ->toBeClasses()
            ->classes()
            ->toHaveSuffix('Data')
            ->toHaveConstructor()
            ->toHaveMethods(['toArray']);

        it('ensures Concerns are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Concerns')
            ->toBeTraits();

        it('ensures Console Commands are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Console\Commands')
            ->toBeClasses()
            ->classes()
            ->toExtend('Illuminate\Console\Command')
            ->toHaveSuffix('Command')
            ->toHaveMethod('handle');

        it('ensures Middlewares are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Http\Middlewares')
            ->toBeClasses()
            ->classes()
            ->toHaveMethod('handle')
            ->toUse([
                'Illuminate\Http\Request',
            ]);

        it('ensures Artifacts are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Artifacts')
            ->toBeClasses()
            ->classes()
            ->toHaveConstructor();

        it('ensures Providers are with correct structure')
            ->expect('JuniorFontenele\LaravelVaultServer\Providers')
            ->toBeClasses()
            ->classes()
            ->toExtend('Illuminate\Support\ServiceProvider')
            ->toHaveMethod('register')
            ->toHaveMethod('boot');
    });

    describe('Security Patterns', function () {
        it('ensures no secrets or passwords in code')
            ->expect(['password', 'secret', 'token', 'key', 'private_key'])
            ->not->toBeUsed()
            ->ignoring([
                'JuniorFontenele\LaravelVaultServer\Models',
                'JuniorFontenele\LaravelVaultServer\Services',
                'JuniorFontenele\LaravelVaultServer\Http\Controllers',
            ]);
    });

    describe('Code Quality', function () {
        it('ensures strict types declaration')
            ->expect('JuniorFontenele\LaravelVaultServer')
            ->toUseStrictTypes();

        it('ensures no debugging functions usage')
            ->expect('JuniorFontenele\LaravelVaultServer')
            ->not->toUse([
                'dd',
                'dump',
                'var_dump',
                'print_r',
                'echo',
                'die',
                'exit',
                'ray',
                'ds',
                'var_export',
            ]);
    });
});
