<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JuniorFontenele\LaravelVaultServer\Services\PepperService;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        Schema::create($tablePrefix . 'peppers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('version')->index();
            $table->longText('value');
            $table->boolean('is_revoked')->index()->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('allowed_scopes');
            $table->boolean('is_active')->index()->default(true);
            $table->string('provision_token')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'keys', function (Blueprint $table) use ($tablePrefix) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained($tablePrefix . 'clients')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('algorithm');
            $table->longText('public_key');
            $table->unsignedBigInteger('version')->index();
            $table->boolean('is_revoked')->index()->default(false);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'hashes', function (Blueprint $table) use ($tablePrefix) {
            $table->uuid('user_id')->primary();
            $table->longText('hash');
            $table->foreignUuid('pepper_id')->constrained($tablePrefix . 'peppers');
            $table->boolean('needs_rehash')->default(false);
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'audit', function (Blueprint $table) use ($tablePrefix) {
            $table->id();
            $table->string('action')->index();
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->foreignUuid('client_id')->nullable()->constrained($tablePrefix . 'clients');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'auditable_type', 'auditable_id']);
        });

        app(PepperService::class)->rotatePepper();
    }

    public function down(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        Schema::dropIfExists($tablePrefix . 'audit');
        Schema::dropIfExists($tablePrefix . 'hashes');
        Schema::dropIfExists($tablePrefix . 'keys');
        Schema::dropIfExists($tablePrefix . 'clients');
        Schema::dropIfExists($tablePrefix . 'peppers');
    }
};
