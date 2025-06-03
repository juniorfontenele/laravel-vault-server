<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Facades\DB;
use JuniorFontenele\LaravelVaultServer\Events\Pepper\PepperRotated;
use JuniorFontenele\LaravelVaultServer\Models\Hash;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;

class PepperService
{
    public function rotatePepper(): Pepper
    {
        $pepper = DB::transaction(function (): Pepper {
            $maxVersion = Pepper::max('version') ?? 0;
            $version = $maxVersion + 1;

            Pepper::query()->update(['is_revoked' => true, 'revoked_at' => now()]);

            Hash::query()
                ->update(['needs_rehash' => true]);

            $pepperValue = bin2hex(random_bytes(16));

            return Pepper::create([
                'version' => $version,
                'value' => $pepperValue,
            ]);
        });

        event(new PepperRotated($pepper));

        return $pepper;
    }

    public function getActive(): Pepper
    {
        return Pepper::where('is_revoked', false)
            ->orderBy('version', 'desc')
            ->sole();
    }

    public function getById(string $id): Pepper
    {
        return Pepper::findOrFail($id);
    }
}
