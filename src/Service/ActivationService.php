<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use Exception;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLoggerRemote\Exception\ModuleActivationException;
use OxidSupport\RequestLoggerRemote\Exception\ModuleDeactivationException;

final readonly class ActivationService implements ActivationServiceInterface
{
    public function __construct(
        private ContextInterface $context,
        private ModuleActivationBridgeInterface $moduleActivationBridge
    ) {
    }

    public function activate(): bool
    {
        $shopId = $this->context->getCurrentShopId();

        try {
            $this->moduleActivationBridge->activate(RequestLoggerModule::ID, $shopId);
        } catch (Exception $exception) {
            throw new ModuleActivationException(
                'Failed to activate module: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        return true;
    }

    public function deactivate(): bool
    {
        $shopId = $this->context->getCurrentShopId();

        try {
            $this->moduleActivationBridge->deactivate(RequestLoggerModule::ID, $shopId);
        } catch (Exception $exception) {
            throw new ModuleDeactivationException(
                'Failed to deactivate module: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        return true;
    }

    public function isActive(): bool
    {
        $shopId = $this->context->getCurrentShopId();

        try {
            return $this->moduleActivationBridge->isActive(RequestLoggerModule::ID, $shopId);
        } catch (Exception $exception) {
            return false;
        }
    }
}
