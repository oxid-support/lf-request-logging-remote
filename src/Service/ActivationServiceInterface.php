<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

interface ActivationServiceInterface
{
    public function activate(): bool;

    public function deactivate(): bool;

    public function isActive(): bool;
}
