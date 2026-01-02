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

    private const GRAPHQL_BASE_MODULE_ID = 'oe_graphql_base';
    private const CONFIG_ACCESS_MODULE_ID = 'oe_graphql_configuration_access';

    /**
     * Check if the module is activated.
     */
    public function isModuleActivated(): bool
    {
        if ($this->getCurrentModuleId() !== Module::MODULE_ID) {
            return false;
        }

        return $this->isModuleActive(Module::MODULE_ID);
    }

    /**
     * Check if GraphQL Base module is activated.
     */
    public function isGraphqlBaseActivated(): bool
    {
        return $this->isModuleActive(self::GRAPHQL_BASE_MODULE_ID);
    }

    /**
     * Check if Configuration Access module is activated.
     */
    public function isConfigAccessActivated(): bool
    {
        return $this->isModuleActive(self::CONFIG_ACCESS_MODULE_ID);
    }

    /**
     * Check if a specific module is activated.
     */
    private function isModuleActive(string $moduleId): bool
    {
        try {
            $shopConfiguration = $this->getShopConfigurationDao()->get(
                $this->getContext()->getCurrentShopId()
            );
            return $shopConfiguration
                ->getModuleConfiguration($moduleId)
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
