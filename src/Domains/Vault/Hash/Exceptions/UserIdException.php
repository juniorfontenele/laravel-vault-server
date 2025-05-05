<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Hash\Exceptions;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;

class UserIdException extends \Exception implements Translatable
{
    use HasTranslations;

    public static function invalidUserId(string $userId): static
    {
        return static::withTranslation('O id :id do usuário é inválido.', [
            'id' => $userId,
        ]);
    }
}
