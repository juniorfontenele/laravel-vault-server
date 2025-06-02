<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Jwt;

class InvalidJwtHeader extends \Exception
{
    public function __construct()
    {
        parent::__construct(__('Cabeçalho JWT inválido'));
    }
}
