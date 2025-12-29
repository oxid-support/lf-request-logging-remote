<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\GraphQL\Base\Framework\GraphQLQueryHandler;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordAlreadySetException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Right;

final class PasswordController
{
    private const USER_ID = 'oxsrequestlogger_api_user';

    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    /**
     * Set the initial password for the Request Logger API user.
     * Requires a valid setup token and only works once - when no password has been set yet.
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

        $userId = md5(self::USER_ID);

        // Check if password is still the placeholder
        if (!$this->isPasswordPlaceholder($userId)) {
            throw new PasswordAlreadySetException();
        }

        /** @var User $user */
        $user = oxNew(User::class);

        if (!$user->load($userId)) {
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
        $userId = md5(self::USER_ID);

        /** @var User $user */
        $user = oxNew(User::class);

        if (!$user->load($userId)) {
            throw new UserNotFoundException();
        }

        // Reset password to a placeholder (random hex string)
        $placeholder = bin2hex(random_bytes(32));
        $this->setPasswordPlaceholder($userId, $placeholder);

        // Generate new setup token
        $token = Registry::getUtilsObject()->generateUId();
        $this->moduleSettingService->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);

        return $token;
    }

    private function isPasswordPlaceholder(string $userId): bool
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXPASSWORD')
            ->from('oxuser')
            ->where('OXID = :userId')
            ->setParameter('userId', $userId);

        $password = $queryBuilder->execute()->fetchOne();

        if (!$password) {
            return true; // No user found, treat as placeholder
        }

        // Check if it's a placeholder (random hex string from migration)
        // Real bcrypt hashes start with $2y$ or similar
        return !str_starts_with($password, '$');
    }

    private function setPasswordPlaceholder(string $userId, string $placeholder): void
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('oxuser')
            ->set('OXPASSWORD', ':placeholder')
            ->set('OXPASSSALT', ':salt')
            ->where('OXID = :userId')
            ->setParameter('placeholder', $placeholder)
            ->setParameter('salt', '')
            ->setParameter('userId', $userId);

        $queryBuilder->execute();
    }
}
