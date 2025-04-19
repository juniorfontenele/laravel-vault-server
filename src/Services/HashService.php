<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Facades\Event;
use JuniorFontenele\LaravelVaultServer\Models\Hash;

class HashService
{
    public function getByUserId(string $userId): ?Hash
    {
        Event::dispatch('vault.hash.get', [$userId]);

        return Hash::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function store(string $clientId, string $userId, string $hash): Hash
    {
        $hashModel = Hash::query()
            ->where('user_id', $userId)
            ->first();

        if ($hashModel) {
            Event::dispatch('vault.hash.updated', [$clientId, $userId]);

            $hashModel->update(['updated_by' => $clientId, 'hash' => $hash]);
            $hashModel->refresh();

            return $hashModel;
        }

        Event::dispatch('vault.hash.created', [$clientId, $userId]);

        return Hash::create([
            'created_by' => $clientId,
            'updated_by' => $clientId,
            'user_id' => $userId,
            'hash' => $hash,
        ]);
    }

    public function delete(string $userId): bool
    {
        $hashModel = Hash::query()
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
