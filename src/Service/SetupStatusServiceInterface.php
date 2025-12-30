<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

interface SetupStatusServiceInterface
{
    /**
     * Check if the module migrations have been executed.
     */
    public function isMigrationExecuted(): bool;
}
