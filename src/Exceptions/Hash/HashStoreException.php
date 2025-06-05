<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Exceptions\Hash;

class HashStoreException extends \Exception
{
    /**
     * Create a new class instance.
     */
    public function __construct(public string $userId)
    {
        parent::__construct(__('Falha ao armazenar o hash para o usuário: :userId', ['userId' => $userId]));
    }
}
