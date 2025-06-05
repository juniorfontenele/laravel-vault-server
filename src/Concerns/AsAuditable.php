<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use JuniorFontenele\LaravelVaultServer\Enums\AuditAction;
use JuniorFontenele\LaravelVaultServer\Models\Audit;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AsAuditable
{
    protected static function bootAsAuditable(): void
    {
        static::created(function ($model) {
            static::createAuditRecord($model, AuditAction::CREATE);
        });

        static::updated(function ($model) {
            static::createAuditRecord($model, AuditAction::UPDATE);
        });

        static::deleted(function ($model) {
            static::createAuditRecord($model, AuditAction::DELETE);
        });

        static::retrieved(function ($model) {
            static::createAuditRecord($model, AuditAction::RETRIEVE);
        });
    }

    protected static function createAuditRecord(self $model, AuditAction $action): void
    {
        $model->auditRecords()->create([
            'action' => $action,
            'context' => $model->getContext(),
        ]);
    }

    /** @return MorphMany<Audit> */
    public function auditRecords(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    /**
     * Get the context for the audit record.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [];
    }
}
