<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Service\SetupStatusServiceInterface;

/**
 * Extended module configuration controller.
 * Provides additional template variables for setup status.
 */
class ModuleConfigController extends ModuleConfiguration
{
    /**
     * Check if the module is activated.
     */
    public function isModuleActivated(): bool
    {
        if ($this->getEditObjectId() !== Module::MODULE_ID) {
            return false;
        }

        try {
            $container = ContainerFactory::getInstance()->getContainer();
            $context = $container->get(ContextInterface::class);
            $shopConfigurationDao = $container->get(ShopConfigurationDaoInterface::class);
            $shopConfiguration = $shopConfigurationDao->get($context->getCurrentShopId());

            return $shopConfiguration->getModuleConfiguration(Module::MODULE_ID)->isActivated();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Check if migrations have been executed.
     */
    public function isMigrationExecuted(): bool
    {
        if ($this->getEditObjectId() !== Module::MODULE_ID) {
            return true;
        }

        try {
            $container = ContainerFactory::getInstance()->getContainer();
            $setupStatusService = $container->get(SetupStatusServiceInterface::class);
            return $setupStatusService->isMigrationExecuted();
        } catch (\Exception) {
            return false;
        }
    }
}
