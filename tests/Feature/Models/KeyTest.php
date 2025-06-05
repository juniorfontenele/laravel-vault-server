<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Models\Key;

covers(Key::class);

describe('Key Model', function () {
    it('checks key validity flags', function () {
        $key = Key::factory()->make([
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'is_revoked' => false,
        ]);

        expect($key->isExpired())->toBeFalse()
            ->and($key->isActive())->toBeTrue()
            ->and($key->isRevoked())->toBeFalse()
            ->and($key->isValid())->toBeTrue()
            ->and($key->isInvalid())->toBeFalse();
    });

    it('detects expired or revoked key', function () {
        $key = Key::factory()->make([
            'valid_from' => now()->subDays(2),
            'valid_until' => now()->subDay(),
            'is_revoked' => true,
        ]);

        expect($key->isExpired())->toBeTrue()
            ->and($key->isValid())->toBeFalse()
            ->and($key->isInvalid())->toBeTrue();
    });
});
