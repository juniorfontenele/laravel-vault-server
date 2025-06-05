<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\JuniorFontenele\LaravelVaultServer\Models\Pepper>
 */
class PepperFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Pepper>
     */
    protected $model = Pepper::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => bin2hex(random_bytes(16)),
        ];
    }
}
