<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        Schema::create($tablePrefix . 'clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->json('allowed_scopes')->nullable();
            $table->boolean('is_active')->index()->default(true);
            $table->string('provision_token')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'keys', function (Blueprint $table) use ($tablePrefix) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained($tablePrefix . 'clients')->cascadeOnDelete()->cascadeOnUpdate();
            $table->longText('public_key');
            $table->unsignedBigInteger('version')->index();
            $table->boolean('is_revoked')->index()->default(false);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tablePrefix . 'hashes', function (Blueprint $table) use ($tablePrefix) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->longText('hash');
            $table->uuid('created_by')->index();
            $table->uuid('updated_by')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        Schema::dropIfExists($tablePrefix . 'hashes');
        Schema::dropIfExists($tablePrefix . 'keys');
        Schema::dropIfExists($tablePrefix . 'clients');
    }
};
