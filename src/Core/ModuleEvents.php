<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;

final class ModuleEvents
{
    /**
     * Called on module activation.
     * Generates a setup token if one doesn't exist yet.
     * The token is used once to set the API user password via GraphQL.
     */
    public static function onActivate(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $moduleSettingService = $container->get(ModuleSettingServiceInterface::class);

        try {
            $currentToken = (string) $moduleSettingService->getString(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID);
        } catch (\Throwable $e) {
            $currentToken = '';
        }

        // Generate token only if not already set (first activation only)
        if (empty($currentToken)) {
            $token = Registry::getUtilsObject()->generateUId();
            $moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);
        }
    }
}
