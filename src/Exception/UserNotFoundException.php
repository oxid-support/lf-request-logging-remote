<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Exception;

use OxidEsales\GraphQL\Base\Exception\Error;

final class UserNotFoundException extends Error
{
    public function __construct()
    {
        parent::__construct('API user not found. Please run the module migrations first.');
    }

    public function getCategory(): string
    {
        return 'notfound';
    }
}
