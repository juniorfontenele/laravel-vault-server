<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\HashModel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<HashModel>
 */
class HashFactory extends Factory
{
    protected $model = HashModel::class;

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
