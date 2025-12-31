<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use OxidSupport\RequestLoggerRemote\Service\TokenGeneratorInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Right;

final class PasswordController
{
    public function __construct(
        private ApiUserServiceInterface $apiUserService,
        private ModuleSettingServiceInterface $moduleSettingService,
        private TokenGeneratorInterface $tokenGenerator
    ) {
    }

    /**
     * Set the password for the Request Logger API user.
     * Requires a valid setup token. Token is invalidated after use.
     */
    #[Mutation]
    public function requestLoggerSetPassword(string $token, string $password): bool
    {
        $this->validateToken($token);
        $this->validatePassword($password);

        // Security: Clear token BEFORE setting password to prevent race conditions (TOCTOU)
        // This ensures a second concurrent request with the same token will fail validation
        $this->moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, '', Module::MODULE_ID);

        // Delegate to service - no more oxNew() or direct User manipulation
        $this->apiUserService->setPasswordForApiUser($password);

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
        // Generate new setup token
        $token = $this->tokenGenerator->generate();

        // Delegate to service - no more oxNew() or Registry calls
        $this->apiUserService->resetPasswordForApiUser();

        // Save token
        $this->moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);

        return $token;
    }

    private function validateToken(string $token): void
    {
        try {
            $storedToken = (string) $this->moduleSettingService->getString(
                Module::SETTING_SETUP_TOKEN,
                Module::MODULE_ID
            );
        } catch (\Throwable) {
            throw new InvalidTokenException();
        }

        // Security: Use constant-time comparison to prevent timing attacks
        // An attacker cannot determine correct token characters by measuring response times
        if (empty($storedToken) || !hash_equals($storedToken, $token)) {
            throw new InvalidTokenException();
        }
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new PasswordTooShortException();
        }
    }
}
