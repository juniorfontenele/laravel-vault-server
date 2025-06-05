<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Enums\Scope;

describe('Scope Enum', function () {
    it('converts scope to label', function () {
        expect(Scope::KEYS_READ->getLabel())->toBe('Read keys');
    });

    it('throws exception on invalid value', function () {
        expect(fn () => Scope::fromString('invalid'))
            ->toThrow(JuniorFontenele\LaravelVaultServer\Exceptions\Client\InvalidScopeException::class);
    });
});
