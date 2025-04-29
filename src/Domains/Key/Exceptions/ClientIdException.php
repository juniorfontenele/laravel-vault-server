<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Key\Exceptions;

use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

class ClientIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidClientId(string $clientId): static
    {
        return static::withTranslation('O id do cliente :id é inválido.', [
            'id' => $clientId,
        ]);
    }
}
