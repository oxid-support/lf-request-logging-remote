<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

interface TokenGeneratorInterface
{
    /**
     * Generate a unique token string.
     *
     * @return string The generated token
     */
    public function generate(): string;
}
