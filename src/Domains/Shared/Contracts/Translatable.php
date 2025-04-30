<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts;

interface Translatable
{
    public function translationKey(): string;

    /**
     * @return array<int|string, mixed>
     */
    public function translationParameters(): array;
}
