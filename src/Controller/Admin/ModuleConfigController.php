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
    private ?ContextInterface $context = null;
    private ?ShopConfigurationDaoInterface $shopConfigurationDao = null;
    private ?SetupStatusServiceInterface $setupStatusService = null;

    /**
     * Check if the module is activated.
     */
    public function isModuleActivated(): bool
    {
        if ($this->getCurrentModuleId() !== Module::MODULE_ID) {
            return false;
        }

        try {
            $shopConfiguration = $this->getShopConfigurationDao()->get(
                $this->getContext()->getCurrentShopId()
            );
            return $shopConfiguration
                ->getModuleConfiguration(Module::MODULE_ID)
                ->isActivated();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Check if migrations have been executed.
     */
    public function isMigrationExecuted(): bool
    {
        if ($this->getCurrentModuleId() !== Module::MODULE_ID) {
            return true;
        }

        try {
            return $this->getSetupStatusService()->isMigrationExecuted();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get the current module ID from edit object.
     * Can be overridden in tests.
     */
    protected function getCurrentModuleId(): string
    {
        return $this->getEditObjectId();
    }

    private function getContext(): ContextInterface
    {
        if ($this->context === null) {
            $this->context = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ContextInterface::class);
        }
        return $this->context;
    }

    private function getShopConfigurationDao(): ShopConfigurationDaoInterface
    {
        if ($this->shopConfigurationDao === null) {
            $this->shopConfigurationDao = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ShopConfigurationDaoInterface::class);
        }
        return $this->shopConfigurationDao;
    }

    private function getSetupStatusService(): SetupStatusServiceInterface
    {
        if ($this->setupStatusService === null) {
            $this->setupStatusService = ContainerFactory::getInstance()
                ->getContainer()
                ->get(SetupStatusServiceInterface::class);
        }
        return $this->setupStatusService;
    }
}
