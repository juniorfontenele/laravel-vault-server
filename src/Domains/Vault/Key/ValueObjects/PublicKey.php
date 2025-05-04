<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\ValueObjects;

use JuniorFontenele\LaravelVaultServer\Domains\Vault\Key\Exceptions\PublicKeyException;

class PublicKey
{
    protected string $value;

    public function __construct(string $value)
    {
        // Validate public key format
        if (! $this->isValidRsaPublicKey($value)) {
            throw PublicKeyException::invalidPublicKey();
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }

    /**
     * Validates the format of an RSA public key.
     *
     * @param string $publicKey The public key to validate
     * @return bool Returns true if the key is valid, false otherwise
     */
    public function isValidRsaPublicKey(string $publicKey): bool
    {
        $publicKey = trim($publicKey);

        // Pattern to validate an RSA public key in PEM format
        $pattern = '/^-----BEGIN PUBLIC KEY-----\s*'
                 . '([A-Za-z0-9+\/=\s]+)'
                 . '-----END PUBLIC KEY-----\s*$/';

        // Check if the key matches the pattern
        if (! preg_match($pattern, $publicKey, $matches)) {
            return false;
        }

        // Check if the content of the key is a valid base64 string
        $keyContent = preg_replace('/\s+/', '', $matches[1]);

        if (base64_decode($keyContent, true) === false) {
            return false;
        }

        return true;
    }
}
