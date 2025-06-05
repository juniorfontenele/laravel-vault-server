<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Hash;

use Exception;

class RehashNeededException extends Exception
{
    protected $message = 'Rehashing is needed.';
}
