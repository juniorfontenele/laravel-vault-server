<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Enums;

enum AuditAction: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case RETRIEVE = 'retrieve';
}
