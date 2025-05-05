<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models\KeyModel;
use phpseclib3\Crypt\RSA;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<KeyModel>
 */
class KeyFactory extends Factory
{
    protected $model = KeyModel::class;

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
            'public_key' => $publicKey,
            'is_revoked' => false,
            'valid_from' => now(),
            'valid_until' => now()->addDays(30),
        ];
    }
}
