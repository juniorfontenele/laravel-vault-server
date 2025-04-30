<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Infrastructure\Laravel\Persistence\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JuniorFontenele\LaravelVaultServer\Database\Factories\KeyFactory;

class KeyModel extends Model
{
    /** @use HasFactory<KeyFactory> */
    use HasFactory;
    use HasUuids;

    public ?string $private_key = null;

    /** @var list<string> */
    protected $fillable = [
        'public_key',
        'revoked',
        'valid_from',
        'valid_until',
    ];

    protected $hidden = [
        'private_key',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $key) {
            $maxVersion = $key->client->keys()
                ->max('version') ?? 0;

            $key->version = $maxVersion + 1;
        });
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'keys';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'revoked' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function newFactory(): KeyFactory
    {
        return KeyFactory::new();
    }

    /** @return BelongsTo<ClientModel> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class);
    }

    /**
     * Revokes the key.
     * This method sets the revoked attribute to true and saves the model.
     * @return bool
     */
    public function revoke(): bool
    {
        $this->revoked = true;
        $this->revoked_at = now();
        $this->save();

        return true;
    }

    /**
     * Checks if the key is expired.
     * The key is considered expired if the current date is greater than the valid_until date.
     * This method does not check if the key is revoked.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->valid_until < now();
    }

    /**
     * Checks if the key is active.
     * The key is considered active if the current date is greater than or equal to the valid_from date.
     * This method does not check if the key is revoked.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->valid_from <= now();
    }

    /**
     * Checks if the key is revoked.
     * The key is considered revoked if the revoked attribute is true.
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    /**
     * Checks if the key is valid.
     * The key is considered valid if it is not revoked, not expired, and active.
     * @return bool
     */
    public function isValid(): bool
    {
        return ! $this->isRevoked()
            && ! $this->isExpired()
            && $this->isActive();
    }

    /**
     * Checks if the key is not valid.
     * The key is considered not valid if it is revoked, expired, or not active.
     * This method is the opposite of isValid().
     * @return bool
     */
    public function isInvalid(): bool
    {
        return ! $this->isValid();
    }

    #[Scope]
    protected function valid(Builder $query): void
    {
        $query->where('revoked', false)
            ->where('valid_until', '>', now())
            ->where('valid_from', '<=', now());
    }

    #[Scope]
    protected function revoked(Builder $query): void
    {
        $query->where('revoked', true);
    }

    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->where('valid_until', '<', now());
    }
}
