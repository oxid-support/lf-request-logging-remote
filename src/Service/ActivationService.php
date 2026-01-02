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
use OxidEsales\GraphQL\ConfigurationAccess\Module\Service\ModuleActivationServiceInterface as ConfigAccessActivationService;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLoggerRemote\Exception\ModuleActivationException;
use OxidSupport\RequestLoggerRemote\Exception\ModuleDeactivationException;

/**
 * Service for managing Request Logger module activation state.
 *
 * This service wraps the official OXID configuration-access module's ModuleActivationService
 * for activate/deactivate operations, while providing an additional isActive() check
 * that is not available in the configuration-access module.
 */
final readonly class ActivationService implements ActivationServiceInterface
{
    public function __construct(
        private ContextInterface $context,
        private ModuleActivationBridgeInterface $moduleActivationBridge,
        private ConfigAccessActivationService $configAccessActivationService
    ) {
    }

    public function activate(): bool
    {
        try {
            return $this->configAccessActivationService->activateModule(RequestLoggerModule::ID);
        } catch (Exception $exception) {
            // Security: Don't expose internal error details to API consumers
            error_log('Module activation failed: ' . $exception->getMessage());

            throw new ModuleActivationException(
                'Failed to activate module',
                0,
                $exception
            );
        }
    }

    public function deactivate(): bool
    {
        try {
            return $this->configAccessActivationService->deactivateModule(RequestLoggerModule::ID);
        } catch (Exception $exception) {
            // Security: Don't expose internal error details to API consumers
            error_log('Module deactivation failed: ' . $exception->getMessage());

            throw new ModuleDeactivationException(
                'Failed to deactivate module',
                0,
                $exception
            );
        }
    }

    /**
     * Check if the Request Logger module is currently active.
     * Note: This method uses the OXID bridge directly as configuration-access
     * does not provide an isActive check.
     */
    public function isActive(): bool
    {
        $shopId = $this->context->getCurrentShopId();

        try {
            return $this->moduleActivationBridge->isActive(RequestLoggerModule::ID, $shopId);
        } catch (Exception) {
            return false;
        }
    }
}
