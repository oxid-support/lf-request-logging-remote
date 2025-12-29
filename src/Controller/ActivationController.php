<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller;

use OxidSupport\RequestLoggerRemote\Service\ActivationServiceInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;

final class ActivationController
{
    public function __construct(
        private ActivationServiceInterface $activationService
    ) {
    }

    /**
     * Check if the request logger module is currently active
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerIsActive(): bool
    {
        return $this->activationService->isActive();
    }

    /**
     * Activate the request logger module
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_ACTIVATE')]
    public function requestLoggerActivate(): bool
    {
        return $this->activationService->activate();
    }

    /**
     * Deactivate the request logger module
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_ACTIVATE')]
    public function requestLoggerDeactivate(): bool
    {
        return $this->activationService->deactivate();
    }
}
