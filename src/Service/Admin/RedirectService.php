<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidSupport\RequestLoggerRemote\Core\Module;

final class RedirectService implements RedirectServiceInterface
{
    public function redirectToModuleConfig(array $params = []): void
    {
        $baseUrl = Registry::getConfig()->getCurrentShopUrl() . 'admin/index.php';
        $params = array_merge([
            'cl' => 'module_config',
            'oxid' => Module::MODULE_ID,
            'force_sid' => Registry::getSession()->getId(),
        ], $params);

        $url = $baseUrl . '?' . http_build_query($params);
        Registry::getUtils()->redirect($url, false, 302);
    }
}
