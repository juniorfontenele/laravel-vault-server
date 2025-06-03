<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelVaultServer\Exceptions\Jwt\InvalidJwtHeader;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use JuniorFontenele\LaravelVaultServer\Services\JwtAuthService;

beforeEach(function () {
    Key::query()->delete();
    Client::query()->delete();
});

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

describe('JwtAuthService', function () {
    it('throws InvalidJwtHeader on invalid token', function () {
        $service = app(JwtAuthService::class);
        $this->expectException(InvalidJwtHeader::class);
        $service->attempt('invalid.token');
    });

    it('authenticates with a valid token', function () {
        $token = $this->getJwtToken();
        $service = app(JwtAuthService::class);
        $key = $service->attempt($token);

        expect($service->check())->toBeTrue()
            ->and($key)->not()->toBeNull();
    });
});
