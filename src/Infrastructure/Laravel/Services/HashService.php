<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Hash\HashResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash\DeleteHashesForUserId;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash\FindHashByUserId;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Hash\StoreHashForUserId;

class HashService
{
    public function getByUserId(string $userId): ?HashResponseDTO
    {
        Event::dispatch('vault.hash.get', [$userId]);

        return app(FindHashByUserId::class)
            ->execute($userId);
    }

    public function store(string $userId, string $hash): HashResponseDTO
    {
        $hashResponseDTO = app(StoreHashForUserId::class)->execute($userId, $hash);

        Event::dispatch('vault.hash.created', [$userId]);

        return $hashResponseDTO;
    }

    public function delete(string $userId): void
    {
        app(DeleteHashesForUserId::class)
            ->execute($userId);

        Event::dispatch('vault.hash.deleted', [$userId]);
    }
}
