<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\Eshop\Core\Registry;

final class TokenGenerator implements TokenGeneratorInterface
{
    public function generate(): string
    {
        return Registry::getUtilsObject()->generateUId();
    }
}
