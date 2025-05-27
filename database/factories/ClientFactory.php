<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JuniorFontenele\LaravelVaultServer\Models\ClientModel;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\JuniorFontenele\LaravelVaultServer\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ClientModel>
     */
    protected $model = ClientModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'https://' . fake()->domainName(),
            'description' => fake()->sentence(),
            'allowed_scopes' => ['keys:read', 'keys:rotate'],
        ];
    }
}
