<?php

declare(strict_types = 1);

use JuniorFontenele\LaravelSecureJwt\CustomClaims;
use JuniorFontenele\LaravelSecureJwt\JwtKey;
use JuniorFontenele\LaravelSecureJwt\Services\JwtService;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;
use phpseclib3\Crypt\RSA;
use Ramsey\Uuid\Uuid;

uses(JuniorFontenele\LaravelVaultServer\Tests\TestCase::class);

beforeEach(function () {
    Pepper::create([
        'version' => 1,
        'value' => 'pepper',
        'is_revoked' => false,
    ]);
});

describe('ValidateJwtToken middleware', function () {
    it('rejects requests without token', function () {
        $response = $this->postJson(route('vault.password.store', ['userId' => 'userX']), [
            'password' => 'secret',
        ]);

        $response->assertUnauthorized();
    });

    it('rejects requests with invalid token', function () {
        $private = RSA::createKey();

        $token = app(JwtService::class)->encode(
            new CustomClaims(),
            new JwtKey(
                id: Uuid::uuid7()->toString(),
                key: $private->toString('PKCS8'),
                algorithm: 'RS256',
            )
        );

        $response = $this->withToken($token)
            ->postJson(route('vault.password.store', ['userId' => 'userX']), [
                'password' => 'secret',
            ]);

        expect($response->status())->toBe(422);
    });
});
// success scenario covered in other integration tests
