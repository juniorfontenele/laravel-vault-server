<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Client\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Client\Enums\Scope;
use JuniorFontenele\LaravelVaultServer\Domains\Client\Exceptions\InvalidScopeException;

class AllowedScopes
{
    /**
     * @var Scope[]
     */
    protected array $scopes = [];

    protected string $separator = ' ';

    /**
     * @param Scope[] $scopes
     */
    final public function __construct(array $scopes)
    {
        foreach ($scopes as $scope) {
            if (! $scope instanceof Scope) { // @phpstan-ignore-line
                throw InvalidScopeException::invalidType();
            }
        }

        $this->scopes = $scopes;
    }

    /**
     * @return Scope[]
     */
    public function all(): array
    {
        return $this->scopes;
    }

    public function has(Scope|string $scope): bool
    {
        if (is_string($scope)) {
            $scope = Scope::fromString($scope);
        }

        return in_array($scope, $this->scopes, true);
    }

    public function add(Scope|string $scope): void
    {
        if (is_string($scope)) {
            $scope = Scope::fromString($scope);
        }

        if (! in_array($scope, $this->scopes, true)) {
            $this->scopes[] = $scope;
        }
    }

    public function remove(Scope|string $scope): void
    {
        if (is_string($scope)) {
            $scope = Scope::fromString($scope);
        }

        $this->scopes = array_filter(
            $this->scopes,
            fn ($existingScope) => $existingScope !== $scope
        );
    }

    /**
     * @param string[] $scopes
     */
    public static function fromStringArray(array $scopes): static
    {
        $scopeEnums = array_map(
            fn ($scope) => Scope::fromString($scope),
            $scopes
        );

        return new static($scopeEnums);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return array_map(
            fn ($scope) => $scope->value,
            $this->scopes
        );
    }

    public function separator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    public function __toString(): string
    {
        return implode($this->separator, $this->toArray());
    }
}
