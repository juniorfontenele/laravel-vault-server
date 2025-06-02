<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Enums;

use JuniorFontenele\LaravelVaultServer\Events\Client\InvalidScopeException;

enum Scope: string
{
    case KEYS_READ = 'keys:read';
    case KEYS_ROTATE = 'keys:rotate';
    case KEYS_DELETE = 'keys:delete';
    case PASSWORDS_VERIFY = 'passwords:verify';
    case PASSWORDS_CREATE = 'passwords:create';
    case PASSWORDS_DELETE = 'passwords:delete';

    public function getLabel(): string
    {
        return match ($this) {
            self::KEYS_READ => 'Read keys',
            self::KEYS_ROTATE => 'Rotate keys',
            self::KEYS_DELETE => 'Delete keys',
            self::PASSWORDS_VERIFY => 'Read passwords',
            self::PASSWORDS_CREATE => 'Create passwords',
            self::PASSWORDS_DELETE => 'Delete passwords',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        $scopes = [];

        foreach (self::cases() as $scope) {
            $scopes[$scope->value] = $scope->getLabel();
        }

        return $scopes;
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidScopeException($value);
    }
}
