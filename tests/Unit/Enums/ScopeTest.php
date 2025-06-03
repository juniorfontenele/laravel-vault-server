<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\Scope;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

it('converts scope to label', function () {
    expect(Scope::KEYS_READ->getLabel())->toBe('Read keys');
});

it('throws exception on invalid value', function () {
    $this->expectException(JuniorFontenele\LaravelVaultServer\Events\Client\InvalidScopeException::class);
    Scope::fromString('invalid');
});
