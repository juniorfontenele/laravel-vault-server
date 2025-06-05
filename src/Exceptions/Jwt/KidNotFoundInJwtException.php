<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Jwt;

class KidNotFoundInJwtException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__('Kid não encontrado no JWT'));
    }
}
