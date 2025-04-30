<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Infrastructure\Persistence\Models\HashModel;

class HashService
{
    public function getByUserId(string $userId): ?HashModel
    {
        Event::dispatch('vault.hash.get', [$userId]);

        return HashModel::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function store(string $clientId, string $userId, string $hash): HashModel
    {
        $hashModel = HashModel::query()
            ->where('user_id', $userId)
            ->first();

        if ($hashModel) {
            Event::dispatch('vault.hash.updated', [$clientId, $userId]);

            $hashModel->update(['updated_by' => $clientId, 'hash' => $hash]);
            $hashModel->refresh();

            return $hashModel;
        }

        Event::dispatch('vault.hash.created', [$clientId, $userId]);

        return HashModel::create([
            'created_by' => $clientId,
            'updated_by' => $clientId,
            'user_id' => $userId,
            'hash' => $hash,
        ]);
    }

    public function delete(string $userId): bool
    {
        $hashModel = HashModel::query()
            ->where('user_id', $userId)
            ->first();

        if (! $hashModel) {
            return false;
        }

        $hashModel->delete();

        Event::dispatch('vault.hash.deleted', [$userId]);

        return true;
    }
}
