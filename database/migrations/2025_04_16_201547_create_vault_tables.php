<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;
use JuniorFontenele\LaravelVaultServer\Models\Client;
use JuniorFontenele\LaravelVaultServer\Models\PrivateKey;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        if (config('vault.server_enabled')) {
            Schema::create($tablePrefix . 'clients', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('description')->nullable();
                $table->json('allowed_scopes')->nullable();
                $table->boolean('is_active')->index()->default(true);
                $table->string('provision_token')->nullable();
                $table->timestamps();
            });

            Schema::create($tablePrefix . 'keys', function (Blueprint $table) use ($tablePrefix) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained($tablePrefix . 'clients')->cascadeOnDelete()->cascadeOnUpdate();
                $table->longText('public_key');
                $table->unsignedBigInteger('version')->index();
                $table->boolean('revoked')->index()->default(false);
                $table->timestamp('valid_from');
                $table->timestamp('valid_until');
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });

            Schema::create($tablePrefix . 'hashes', function (Blueprint $table) use ($tablePrefix) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained($tablePrefix . 'clients')->cascadeOnUpdate();
                $table->uuid('user_id')->index();
                $table->longText('hash');
                $table->timestamps();
            });
        }

        Schema::create($tablePrefix . 'private_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->index();
            $table->longText('private_key');
            $table->longText('public_key');
            $table->unsignedBigInteger('version')->index();
            $table->boolean('revoked')->index()->default(false);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        if (config('vault.server_enabled')) {
            $client = Client::create([
                'name' => 'Vault',
                'description' => 'Default Vault Client',
                'allowed_scopes' => ['*'],
            ]);

            [$key, $privateKey] = VaultKey::createKeyForClient($client, 2048, 365);

            PrivateKey::create([
                'id' => $key->id,
                'client_id' => $client->id,
                'private_key' => $privateKey,
                'public_key' => $key->public_key,
                'version' => $key->version,
                'valid_from' => $key->valid_from,
                'valid_until' => $key->valid_until,
            ]);
        }
    }

    public function down(): void
    {
        $tablePrefix = config('vault.migrations.table_prefix', 'vault_');

        Schema::dropIfExists($tablePrefix . 'private_keys');
        Schema::dropIfExists($tablePrefix . 'hashes');
        Schema::dropIfExists($tablePrefix . 'keys');
        Schema::dropIfExists($tablePrefix . 'clients');
    }
};
