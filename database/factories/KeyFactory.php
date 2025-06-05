<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Models\Key;
use phpseclib3\Crypt\RSA;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Key>
 */
class KeyFactory extends Factory
{
    protected $model = Key::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $privateKey = RSA::createKey(2048);
        $publicKey = $privateKey->getPublicKey()->toString('PKCS8');

        return [
            'algorithm' => 'RS256',
            'version' => 1,
            'public_key' => $publicKey,
            'is_revoked' => false,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
        ];
    }
}
