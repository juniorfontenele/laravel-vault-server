<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Key;

class KeyNotFoundException extends \Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(public string $keyId)
    {
        parent::__construct(__('Chave com id :id não encontrada.', [
            'id' => $keyId,
        ]));
    }
}
