<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Right;

final class PasswordController
{
    public function __construct(
        private ApiUserServiceInterface $apiUserService,
        private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    /**
     * Set the password for the Request Logger API user.
     * Requires a valid setup token. Token is invalidated after use.
     */
    #[Mutation]
    public function requestLoggerSetPassword(string $token, string $password): bool
    {
        // Get stored token (generated during module activation)
        try {
            $storedToken = (string) $this->moduleSettingService->getString(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID);
        } catch (\Throwable $e) {
            $storedToken = '';
        }

        if (empty($storedToken) || $token !== $storedToken) {
            throw new InvalidTokenException();
        }

        if (strlen($password) < 8) {
            throw new PasswordTooShortException();
        }

        /** @var User $user */
        $user = oxNew(User::class);

        if (!$this->apiUserService->loadApiUser($user)) {
            throw new UserNotFoundException();
        }

        $user->setPassword($password);
        $user->save();

        // Delete token after successful password set
        $this->moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, '', Module::MODULE_ID);

        return true;
    }

    /**
     * Reset the password for the Request Logger API user to a placeholder value.
     * This generates a new setup token that can be used with requestLoggerSetPassword.
     * Requires admin authentication.
     */
    #[Mutation]
    #[Logged]
    #[Right('OXSREQUESTLOGGER_PASSWORD_RESET')]
    public function requestLoggerResetPassword(): string
    {
        /** @var User $user */
        $user = oxNew(User::class);

        if (!$this->apiUserService->loadApiUser($user)) {
            throw new UserNotFoundException();
        }

        $this->apiUserService->resetPassword($user->getId());

        // Generate new setup token
        $token = Registry::getUtilsObject()->generateUId();
        $this->moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);

        return $token;
    }
}
