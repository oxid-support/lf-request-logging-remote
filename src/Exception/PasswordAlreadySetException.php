<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Exception;

use OxidEsales\GraphQL\Base\Exception\Error;

final class PasswordAlreadySetException extends Error
{
    public function __construct()
    {
        parent::__construct('Password has already been set. Use the standard password reset flow to change it.');
    }

    public function getCategory(): string
    {
        return 'permission';
    }
}
