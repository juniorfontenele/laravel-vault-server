<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JuniorFontenele\LaravelVaultServer\Database\Factories\KeyFactory;

/**
 * @property-read string $id
 * @property string $client_id
 * @property string $public_key
 * @property bool $is_revoked
 * @property int $version
 * @property CarbonImmutable|null $valid_from
 * @property CarbonImmutable|null $valid_until
 * @property CarbonImmutable|null $revoked_at
 * @property-read Client $client
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class KeyModel extends Model
{
    /** @use HasFactory<KeyFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'public_key',
        'is_revoked',
        'valid_from',
        'valid_until',
    ];

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'keys';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_revoked' => 'boolean',
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): KeyFactory
    {
        return KeyFactory::new();
    }

    /** @return BelongsTo<Client> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
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
        return $this->is_revoked;
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
}
