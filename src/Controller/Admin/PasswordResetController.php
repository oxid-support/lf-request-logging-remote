<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use OxidSupport\RequestLoggerRemote\Service\TokenGeneratorInterface;

/**
 * Admin controller for password reset functionality.
 *
 * This controller handles the POST action and uses OXID's internal redirect
 * mechanism by returning a controller string from the action method.
 */
final class PasswordResetController extends AdminController
{
    private ?ApiUserServiceInterface $apiUserService = null;
    private ?ModuleSettingServiceInterface $moduleSettingService = null;
    private ?TokenGeneratorInterface $tokenGenerator = null;

    /**
     * Resets the API user password to a placeholder and generates a new setup token.
     *
     * Returns a redirect string to module_config which OXID's BaseController::executeFunction()
     * processes via executeNewAction() - preserving session and admin context.
     */
    public function resetPassword(): string
    {
        try {
            // Generate new setup token
            $token = $this->getTokenGenerator()->generate();

            // Reset password via service
            $this->getApiUserService()->resetPasswordForApiUser();

            // Save token
            $this->getModuleSettingService()->saveString(
                Module::SETTING_SETUP_TOKEN,
                $token,
                Module::MODULE_ID
            );

            // Return redirect string - OXID handles session preservation
            return 'module_config?oxid=' . Module::MODULE_ID . '&resetSuccess=1&newToken=' . $token;
        } catch (UserNotFoundException) {
            // Return redirect string with error
            return 'module_config?oxid=' . Module::MODULE_ID . '&resetError=USER_NOT_FOUND';
        }
    }

    private function getApiUserService(): ApiUserServiceInterface
    {
        if ($this->apiUserService === null) {
            $this->apiUserService = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ApiUserServiceInterface::class);
        }
        return $this->apiUserService;
    }

    private function getModuleSettingService(): ModuleSettingServiceInterface
    {
        if ($this->moduleSettingService === null) {
            $this->moduleSettingService = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ModuleSettingServiceInterface::class);
        }
        return $this->moduleSettingService;
    }

    private function getTokenGenerator(): TokenGeneratorInterface
    {
        if ($this->tokenGenerator === null) {
            $this->tokenGenerator = ContainerFactory::getInstance()
                ->getContainer()
                ->get(TokenGeneratorInterface::class);
        }
        return $this->tokenGenerator;
    }
}
