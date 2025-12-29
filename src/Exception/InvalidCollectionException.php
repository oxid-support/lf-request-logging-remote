<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Exception;

use Exception;
use GraphQL\Error\ClientAware;

final class InvalidCollectionException extends Exception implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
