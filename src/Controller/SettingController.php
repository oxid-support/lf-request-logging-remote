<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller;

use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\BooleanSetting;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\SettingType;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\StringSetting;
use OxidSupport\RequestLoggerRemote\Service\SettingServiceInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;

final class SettingController
{
    public function __construct(
        private SettingServiceInterface $settingService
    ) {
    }

    /**
     * Get all request logger settings with their types
     *
     * @return SettingType[]
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerSettings(): array
    {
        return $this->settingService->getAllSettings();
    }

    /**
     * Get the current log level setting
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerLogLevel(): StringSetting
    {
        return $this->settingService->getLogLevel();
    }

    /**
     * Get the log-frontend enabled setting
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerLogFrontend(): BooleanSetting
    {
        return $this->settingService->isLogFrontendEnabled();
    }

    /**
     * Get the log-admin enabled setting
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerLogAdmin(): BooleanSetting
    {
        return $this->settingService->isLogAdminEnabled();
    }

    /**
     * Get the redact items collection (JSON-encoded array)
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerRedact(): StringSetting
    {
        return $this->settingService->getRedactItems();
    }

    /**
     * Get the redact-all-values enabled setting
     */
    #[Query]
    #[Logged]
    #[Right('REQUEST_LOGGER_VIEW')]
    public function requestLoggerRedactAllValues(): BooleanSetting
    {
        return $this->settingService->isRedactAllValuesEnabled();
    }

    /**
     * Change the log level setting
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_CHANGE')]
    public function requestLoggerLogLevelChange(string $value): StringSetting
    {
        return $this->settingService->setLogLevel($value);
    }

    /**
     * Change the log-frontend enabled setting
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_CHANGE')]
    public function requestLoggerLogFrontendChange(bool $value): BooleanSetting
    {
        return $this->settingService->setLogFrontendEnabled($value);
    }

    /**
     * Change the log-admin enabled setting
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_CHANGE')]
    public function requestLoggerLogAdminChange(bool $value): BooleanSetting
    {
        return $this->settingService->setLogAdminEnabled($value);
    }

    /**
     * Change the redact items collection (expects JSON array string)
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_CHANGE')]
    public function requestLoggerRedactChange(string $value): StringSetting
    {
        return $this->settingService->setRedactItems($value);
    }

    /**
     * Change the redact-all-values enabled setting
     */
    #[Mutation]
    #[Logged]
    #[Right('REQUEST_LOGGER_CHANGE')]
    public function requestLoggerRedactAllValuesChange(bool $value): BooleanSetting
    {
        return $this->settingService->setRedactAllValuesEnabled($value);
    }
}
