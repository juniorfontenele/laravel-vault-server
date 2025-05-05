<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\IAM\Client\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\ValueObjects\ProvisionToken;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class ProvisionTokenTest extends TestCase
{
    public function testCreateProvisionToken(): void
    {
        $token = new ProvisionToken();

        $this->assertNotEmpty($token->plainValue());
    }

    public function testVerifyToken(): void
    {
        $token = new ProvisionToken();
        $plainValue = $token->plainValue();

        $this->assertTrue($token->verify($plainValue));
        $this->assertFalse($token->verify('invalid-token'));
    }

    public function testVerifyTokenWithTokenObject(): void
    {
        $token1 = new ProvisionToken();
        $token2 = new ProvisionToken();

        $this->assertTrue($token1->verify($token1->plainValue()));
        $this->assertFalse($token1->verify($token2->plainValue()));
    }
}
