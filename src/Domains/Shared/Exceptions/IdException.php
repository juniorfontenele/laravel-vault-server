<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Shared\Exceptions;

use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

class IdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidUuid(string $id): static
    {
        return static::withTranslation('O id :id não é um UUID válido', [
            'id' => $id,
        ]);
    }
}
