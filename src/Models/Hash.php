<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JuniorFontenele\LaravelVaultServer\Concerns\AsAuditable;
use JuniorFontenele\LaravelVaultServer\Database\Factories\HashFactory;

/**
 * @property-read string $user_id
 * @property string $hash
 * @property string $pepper_id
 * @property-read Pepper|null $pepper
 * @property bool $needs_rehash
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class Hash extends Model
{
    /** @use HasFactory<HashFactory> */
    use HasFactory;
    use AsAuditable;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'hash',
        'pepper_id',
    ];

    protected $hidden = [
        'hash',
        'pepper_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'needs_rehash' => 'boolean',
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

    /** @return BelongsTo<Pepper> */
    public function pepper(): BelongsTo
    {
        return $this->belongsTo(Pepper::class, 'pepper_id');
    }
}
