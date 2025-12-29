<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Exception;

use OxidEsales\GraphQL\Base\Exception\Error;

final class InvalidTokenException extends Error
{
    public function __construct()
    {
        parent::__construct('Invalid or expired setup token.');
    }

    public function getCategory(): string
    {
        return 'permission';
    }
}
