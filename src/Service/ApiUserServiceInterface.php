<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\Eshop\Application\Model\User;

interface ApiUserServiceInterface
{
    /**
     * Load the API user by email.
     */
    public function loadApiUser(User $user): bool;

    /**
     * Reset the password to a placeholder value.
     */
    public function resetPassword(string $userId): void;

    /**
     * Set the password for the API user.
     * Throws UserNotFoundException if user cannot be loaded.
     *
     * @throws \OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException
     */
    public function setPasswordForApiUser(string $password): void;

    /**
     * Reset the password for the API user to a placeholder value.
     * Throws UserNotFoundException if user cannot be loaded.
     *
     * @throws \OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException
     */
    public function resetPasswordForApiUser(): void;
}
