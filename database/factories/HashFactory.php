<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Models\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Hash>
 */
class HashFactory extends Factory
{
    protected $model = Hash::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->uuid(),
            'hash' => bcrypt('password'),
        ];
    }
}
