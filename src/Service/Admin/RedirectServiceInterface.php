<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service\Admin;

interface RedirectServiceInterface
{
    /**
     * Redirect to module configuration page with parameters.
     *
     * @param array<string, string> $params Additional parameters for the URL
     */
    public function redirectToModuleConfig(array $params = []): void;
}
