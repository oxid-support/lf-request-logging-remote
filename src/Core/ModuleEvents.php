<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;

final class ModuleEvents
{

    /**
     * Called on module activation.
     * Generates a setup token only if:
     * - Token doesn't exist yet, AND
     * - Password is not yet set (still placeholder)
     *
     * This prevents generating a new token when reactivating after successful setup.
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

        // Don't generate token if one already exists
        if (!empty($currentToken)) {
            return;
        }

        // Don't generate token if password is already set (BCrypt hash)
        if (self::isPasswordAlreadySet($container)) {
            return;
        }

        // Generate new token for first-time setup
        $token = Registry::getUtilsObject()->generateUId();
        $moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);
    }

    /**
     * Check if the API user has a real password (BCrypt) instead of a placeholder.
     */
    private static function isPasswordAlreadySet($container): bool
    {
        try {
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();
            $queryBuilder
                ->select('OXPASSWORD')
                ->from('oxuser')
                ->where('OXUSERNAME = :email')
                ->setParameter('email', Module::API_USER_EMAIL);

            $password = $queryBuilder->execute()->fetchOne();

            // BCrypt hashes start with $2y$, $2a$, or $2b$
            return $password && str_starts_with($password, '$');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
