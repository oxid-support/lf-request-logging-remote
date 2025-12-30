<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Framework;

use OxidEsales\GraphQL\Base\Framework\PermissionProviderInterface;

final class PermissionProvider implements PermissionProviderInterface
{
    public function getPermissions(): array
    {
        return [
            // Custom user group for Request Logger Remote API access
            'oxsrequestlogger_api' => [
                'REQUEST_LOGGER_VIEW',
                'REQUEST_LOGGER_CHANGE',
                'REQUEST_LOGGER_ACTIVATE',
                'OXSREQUESTLOGGER_PASSWORD_RESET',
            ],
            // Also grant permissions to shop admins
            'oxidadmin' => [
                'REQUEST_LOGGER_VIEW',
                'REQUEST_LOGGER_CHANGE',
                'REQUEST_LOGGER_ACTIVATE',
                'OXSREQUESTLOGGER_PASSWORD_RESET',
            ],
        ];
    }
}
