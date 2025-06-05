# Laravel Vault Server

[![Latest Version on Packagist](https://img.shields.io/packagist/v/juniorfontenele/laravel-vault-server.svg?style=flat-square)](https://packagist.org/packages/juniorfontenele/laravel-vault-server)
[![Tests](https://img.shields.io/github/actions/workflow/status/juniorfontenele/laravel-vault-server/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/juniorfontenele/laravel-vault-server/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/juniorfontenele/laravel-vault-server.svg?style=flat-square)](https://packagist.org/packages/juniorfontenele/laravel-vault-server)

A comprehensive vault server package for Laravel applications that provides secure credential storage, JWT-based authentication with asymmetric keys, and cryptographic key management. Built with security-first principles, this package offers hash storage with salt + pepper, client management, and automatic key rotation capabilities.

## Features

- **Secure Hash Storage**: Store hashes with salt + pepper for enhanced security
- **JWT Authentication**: Asymmetric key-based JWT authentication system
- **Client Management**: Create, provision, and manage vault clients
- **Key Pair Management**: Generate, rotate, and revoke cryptographic key pairs
- **Automatic Cleanup**: Built-in cleanup for expired and revoked keys

## Installation

You can install the package via composer:

```bash
composer require juniorfontenele/laravel-vault-server
```

After installation, run the install command to set up the package:

```bash
php artisan vault-server:install
```

This command will:
- Publish the migration files
- Optionally run the migrations

## Configuration

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=vault-config
```

## Usage

### Using Facades

The package provides several facades for easy access to vault functionality:

#### VaultAuth Facade
*JWT authentication service for client authentication and authorization.*

```php
use JuniorFontenele\LaravelVaultServer\Facades\VaultAuth;

// Authenticate client with JWT token
$key = VaultAuth::attempt($token); // Returns: Key instance

// Check if client is authenticated
$isAuthenticated = VaultAuth::check(); // Returns: bool

// Check if client has specific scope
$canRead = VaultAuth::can('keys:read'); // Returns: bool

// Authorize client for specific scope (throws exception if not authorized)
VaultAuth::authorize('keys:read'); // Returns: void

// Get authenticated client
$client = VaultAuth::client(); // Returns: Client|null

// Get authentication key
$key = VaultAuth::key(); // Returns: Key|null

// Logout client
VaultAuth::logout(); // Returns: void
```

#### VaultClientManager Facade
*Client management service for creating, provisioning, and managing vault clients.*

```php
use JuniorFontenele\LaravelVaultServer\Facades\VaultClientManager;
use JuniorFontenele\LaravelVaultServer\Enums\Scope;

// Create a new client
$newClient = VaultClientManager::createClient(
    name: 'My Application',
    allowedScopes: [Scope::KEYS_READ->value, Scope::KEYS_ROTATE->value],
    description: 'Application description'
);
// Returns: NewClient { client: {id: "cl_123", name: "My Application"}, plaintext_provision_token: "tok_abc" }

// Provision an existing client
$provisionedClient = VaultClientManager::provisionClient($clientId, $provisionToken);
// Returns: Client instance

// Delete a client
VaultClientManager::deleteClient($clientId);
// Returns: void

// Cleanup inactive clients
$deletedClients = VaultClientManager::cleanupInactiveClients();
// Returns: int (number of deleted clients)
```

#### VaultHash Facade
*Secure password storage and validation service using salt + pepper hashing.*

```php
use JuniorFontenele\LaravelVaultServer\Facades\VaultHash;

// Store a password hash with salt + pepper
VaultHash::store($userId, $password);

// Verify a password against stored hash
$isValid = VaultHash::verify($userId, $password);
// Returns: bool

// Delete a stored password hash
VaultHash::delete($userId);
```

#### VaultKey Facade
*Cryptographic key pair management service for JWT signing and verification.*

```php
use JuniorFontenele\LaravelVaultServer\Facades\VaultKey;

// Create a new key pair
$newKey = VaultKey::create(
    clientId: $clientId,
    keySize: 2048,
    expiresIn: 365 // days
);
// Returns: NewKey { key: {id: "key_123", public_key: "-----BEGIN PUBLIC KEY-----...", algorithm: "RS256"}, private_key: "-----BEGIN PRIVATE KEY-----..." }

// Get a key by ID
$key = VaultKey::getById($keyId);
// Returns: Key instance

// Revoke a key
VaultKey::revoke($keyId);

// Cleanup expired keys
$expiredKeys = VaultKey::cleanupExpiredKeys();
// Returns: collection of expired keys

// Cleanup revoked keys
$revokedKeys = VaultKey::cleanupRevokedKeys();
// Returns: collection of revoked keys
```

### Using Artisan Commands

The package provides several Artisan commands for managing clients and keys:

#### Client Management Commands
*Commands for managing vault clients through the command line.*

```bash
# Install the vault server (publishes migrations and optionally runs them)
php artisan vault-server:install

# Create a new client (interactive or with parameters)
php artisan vault-server:client create

# Create a client with parameters
php artisan vault-server:client create \
    --name="My App" \
    --description="Application description" \
    --scopes="keys:read,keys:rotate"

# List all clients
php artisan vault-server:client list

# Delete a client (interactive)
php artisan vault-server:client delete

# Provision a client (interactive)
php artisan vault-server:client provision

# Cleanup inactive clients
php artisan vault-server:client cleanup
```

#### Key Management Commands
*Commands for managing cryptographic key pairs.*

```bash
# Generate a new key pair (interactive)
php artisan vault-server:key generate

# Rotate a key (creates new key, interactive)
php artisan vault-server:key rotate

# List keys for a client (interactive)
php artisan vault-server:key list

# Revoke a key (interactive)
php artisan vault-server:key revoke

# Cleanup expired and revoked keys
php artisan vault-server:key cleanup
```

### Events

The package dispatches various events that you can listen to for auditing and monitoring:

#### Client Events

- `ClientCreated` - When a new client is created
- `ClientDeleted` - When a client is deleted
- `ClientProvisioned` - When a client is provisioned
- `ClientTokenGenerated` - When a JWT token is generated for a client
- `InactiveClientsCleanup` - When inactive clients are cleaned up

#### Hash Events

- `HashStored` - When a hash is stored
- `HashVerified` - When a hash is verified
- `HashDeleted` - When a hash is deleted
- `RehashNeeded` - When a hash needs to be rehashed

#### Key Events

- `KeyCreated` - When a new key pair is created
- `KeyRetrieved` - When a key is retrieved
- `KeyRevoked` - When a key is revoked
- `KeyRotated` - When a key is rotated
- `ExpiredKeysCleanedUp` - When expired keys are cleaned up
- `RevokedKeysCleanedUp` - When revoked keys are cleaned up

#### Pepper Events

- `PepperRotated` - When the pepper is rotated
- `PepperDecryptionFailed` - When pepper decryption fails

#### Example Event Listener

```php
use JuniorFontenele\LaravelVaultServer\Events\Client\ClientCreated;

class ClientCreatedListener
{
    public function handle(ClientCreated $event): void
    {
        // Log the client creation
        Log::info('New vault client created', [
            'client_id' => $event->client->id,
            'client_name' => $event->client->name,
        ]);
        
        // Send notification
        // Perform additional actions
    }
}
```

## API Routes

The package automatically registers API routes for vault operations. By default, routes are registered under the `/vault` prefix. You can access:

- `POST /vault/client/{clientId}/provision` - Provision a client
- `POST /vault/password/{userId}` - Securely store a password
- `POST /vault/password/{userId}/verify` - Verify a password
- `DELETE /vault/password/{userId}` - Delete a password
- `POST /vault/kms/rotate` - Rotate client key pair
- `GET /vault/kms/{kid}` - Get key by ID

## Middleware

The package includes JWT validation middleware that you can use to protect your routes:

```php
// Basic JWT authentication
Route::middleware('vault.jwt')->group(function () {
    // Protected routes here
});

// JWT authentication with scope validation
Route::middleware(['vault.jwt:keys:read'])->group(function () {
    // Routes requiring 'keys:read' scope
});
```

## Testing

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Code Quality

The package includes several code quality tools:

```bash
# Run all quality checks
composer lint

# Format code
composer format

# Static analysis
composer analyze

# Refactor code
composer rector
```

## Security

This package implements several security best practices:

- **Asymmetric JWT**: Uses RSA keys for JWT signing and verification
- **Salt + Pepper**: Hashes are stored with both salt and pepper for enhanced security
- **Key Rotation**: Supports automatic key rotation and cleanup
- **Scope-based Access**: Client access is controlled via scopes
- **Audit Trail**: Comprehensive event system for monitoring

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/juniorfontenele/laravel-vault-server/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/juniorfontenele/laravel-vault-server/security/policy) on how to report security vulnerabilities.

## Credits

- [Junior Fontenele](https://github.com/juniorfontenele)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
