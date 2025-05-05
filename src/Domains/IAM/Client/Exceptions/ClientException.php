<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions;

use Exception;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;

class ClientException extends Exception implements Translatable
{
    use HasTranslations;

    public static function alreadyProvisioned(string $clientId): static
    {
        return static::withTranslation('Cliente :id já foi provisionado', [
            'id' => $clientId,
        ]);
    }

    public static function invalidProvisionToken(string $clientId): static
    {
        return static::withTranslation('Token de provisionamento inválido para o cliente :id', [
            'id' => $clientId,
        ]);
    }

    public static function notFound(string $clientId): static
    {
        return static::withTranslation('Cliente :id não encontrado', [
            'id' => $clientId,
        ]);
    }
}
