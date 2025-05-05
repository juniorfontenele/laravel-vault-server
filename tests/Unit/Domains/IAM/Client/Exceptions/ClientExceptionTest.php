<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client\Exceptions;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ClientExceptionTest extends TestCase
{
    public function testAlreadyProvisioned(): void
    {
        $exception = ClientException::alreadyProvisioned('test-client-id');

        $this->assertEquals('Cliente test-client-id já foi provisionado', $exception->getMessage());
        $this->assertEquals('Cliente :id já foi provisionado', $exception->translationKey());
        $this->assertEquals(['id' => 'test-client-id'], $exception->translationParameters());
    }

    public function testInvalidProvisionToken(): void
    {
        $exception = ClientException::invalidProvisionToken('test-client-id');

        $this->assertEquals('Token de provisionamento inválido para o cliente test-client-id', $exception->getMessage());
        $this->assertEquals('Token de provisionamento inválido para o cliente :id', $exception->translationKey());
        $this->assertEquals(['id' => 'test-client-id'], $exception->translationParameters());
    }

    public function testNotFound(): void
    {
        $exception = ClientException::notFound('test-client-id');

        $this->assertEquals('Cliente test-client-id não encontrado', $exception->getMessage());
        $this->assertEquals('Cliente :id não encontrado', $exception->translationKey());
        $this->assertEquals(['id' => 'test-client-id'], $exception->translationParameters());
    }
}
