<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts;

interface UnitOfWorkInterface
{
    public function execute(callable $operations);
}
