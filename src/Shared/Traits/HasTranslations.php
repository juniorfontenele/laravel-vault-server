<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Shared\Traits;

trait HasTranslations
{
    protected string $translationKey;

    /**
     * @var array<int|string, mixed>
     */
    protected array $translationParameters = [];

    /**
     * @param string $translationKey
     * @param array<int|string, mixed> $translationParameters
     */
    public static function withTranslation(string $translationKey, array $translationParameters = []): self
    {
        $message = $translationKey;

        if ($translationParameters !== []) {
            foreach ($translationParameters as $key => $value) {
                if (is_string($key)) {
                    $message = str_replace(':' . $key, (string) $value, $message);
                } else {
                    $message = str_replace(':value', (string) $value, $message);
                }
            }
        }

        $instance = new static($message);
        $instance->translationKey = $translationKey;
        $instance->translationParameters = $translationParameters;

        return $instance;
    }

    public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function translationParameters(): array
    {
        return $this->translationParameters;
    }
}
