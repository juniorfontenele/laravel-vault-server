<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Application\UseCases\Client;

use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyDTO;
use JuniorFontenele\LaravelVaultServer\Application\DTOs\Key\CreateKeyResponseDTO;
use JuniorFontenele\LaravelVaultServer\Application\UseCases\Key\CreateKeyForClientUseCase;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Client;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Contracts\ClientRepositoryInterface;
use JuniorFontenele\LaravelVaultServer\Domains\IAM\Client\Exceptions\ClientException;
use JuniorFontenele\LaravelVaultServer\Domains\Shared\Contracts\UnitOfWorkInterface;

class ProvisionClientUseCase
{
    public function __construct(
        protected readonly ClientRepositoryInterface $clientRepository,
        protected readonly UnitOfWorkInterface $unitOfWork,
    ) {
    }

    public function execute(string $clientId, string $provisionToken): CreateKeyResponseDTO
    {
        $client = $this->clientRepository->findClientByClientId($clientId);

        if (! $client instanceof Client) {
            throw ClientException::notFound(
                clientId: $clientId,
            );
        }

        if ($client->isProvisioned()) {
            throw ClientException::alreadyProvisioned(
                clientId: $client->clientId(),
            );
        }

        if (! $client->verifyProvisionToken($provisionToken)) {
            throw ClientException::invalidProvisionToken(
                clientId: $client->clientId(),
            );
        }

        return $this->unitOfWork->execute(function () use ($client, $clientId, $provisionToken) {
            $client->provision($provisionToken);

            $this->clientRepository->save($client);

            return app(CreateKeyForClientUseCase::class)->execute(new CreateKeyDTO(
                clientId: $clientId,
            ));
        });
    }
}
