<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JuniorFontenele\LaravelVaultServer\Database\Factories\HashFactory;

/**
 * @property-read string $user_id
 * @property string $hash
 * @property bool $is_revoked
 * @property CarbonImmutable|null $revoked_at
 * @property int $version
 * @property-read Client $creator
 * @property-read Client $updater
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class Hash extends Model
{
    /** @use HasFactory<HashFactory> */
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'hash',
    ];

    protected $hidden = [
        'hash',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_revoked' => 'boolean',
            'revoked_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function getTable(): string
    {
        return config('vault.migrations.table_prefix', 'vault_') . 'hashes';
    }

    protected static function newFactory(): HashFactory
    {
        return HashFactory::new();
    }

    /**
     * @return BelongsTo<Client>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'created_by');
    }

    /**
     * @return BelongsTo<Client>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'updated_by');
    }
}
