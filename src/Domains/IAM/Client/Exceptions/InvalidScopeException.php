<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions;

use InvalidArgumentException;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Shared\Traits\HasTranslations;

final class InvalidScopeException extends InvalidArgumentException implements Translatable
{
    use HasTranslations;

    public static function invalidType(): static
    {
        return static::withTranslation('O escopo precisa ser do tipo ' . Scope::class);
    }

    public static function invalidScope(string $scope): static
    {
        return static::withTranslation('O escopo :scope não é válido.', ['scope' => $scope]);
    }
}
