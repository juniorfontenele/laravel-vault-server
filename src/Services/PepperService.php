<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Services;

use Illuminate\Support\Facades\DB;
use JuniorFontenele\LaravelVaultServer\Events\Pepper\PepperRotated;
use JuniorFontenele\LaravelVaultServer\Models\Pepper;

class PepperService
{
    public function rotatePepper(): Pepper
    {
        $pepper = DB::transaction(function (): Pepper {
            $maxVersion = Pepper::max('version') ?? 0;

            Pepper::query()->update(['is_revoked' => true, 'revoked_at' => now()]);

            $pepperValue = bin2hex(random_bytes(16));

            return Pepper::create([
                'version' => $maxVersion + 1,
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
}
