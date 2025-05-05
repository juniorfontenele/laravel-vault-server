<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Tests\Unit\Domains\Shared\Traits;

use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\Translatable;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Traits\HasTranslations;
use JuniorFontenele\LaravelVaultServer\Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    public function testWithTranslation(): void
    {
        $exception = MockException::withTranslation('Hello :name', ['name' => 'World']);

        $this->assertEquals('Hello World', $exception->getMessage());
        $this->assertEquals('Hello :name', $exception->translationKey());
        $this->assertEquals(['name' => 'World'], $exception->translationParameters());
    }

    public function testWithTranslationWithNumericKeys(): void
    {
        $exception = MockException::withTranslation('Hello :value', [0 => 'World']);

        $this->assertEquals('Hello World', $exception->getMessage());
    }

    public function testWithTranslationWithNoParameters(): void
    {
        $exception = MockException::withTranslation('Hello World');

        $this->assertEquals('Hello World', $exception->getMessage());
        $this->assertEquals('Hello World', $exception->translationKey());
        $this->assertEquals([], $exception->translationParameters());
    }
}

class MockException extends \Exception implements Translatable
{
    use HasTranslations;
}
