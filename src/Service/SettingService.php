<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\GraphQL\ConfigurationAccess\Module\Service\ModuleSettingServiceInterface as ConfigAccessSettingService;
use OxidSupport\RequestLoggerRemote\DataType\SettingType;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLoggerRemote\Exception\InvalidCollectionException;

/**
 * Service for managing Request Logger module settings.
 *
 * This service wraps the official OXID configuration-access module's ModuleSettingService,
 * providing a simplified API specifically for the Request Logger module. This approach:
 * - Avoids code duplication by delegating to the official module
 * - Maintains custom permission control via the oxsrequestlogger_api group
 * - Provides a moduleId-free API for easier consumption
 */
final readonly class SettingService implements SettingServiceInterface
{
    private const SETTING_LOG_LEVEL = RequestLoggerModule::ID . '_log-level';
    private const SETTING_LOG_FRONTEND = RequestLoggerModule::ID . '_log-frontend';
    private const SETTING_LOG_ADMIN = RequestLoggerModule::ID . '_log-admin';
    private const SETTING_REDACT = RequestLoggerModule::ID . '_redact';
    private const SETTING_REDACT_ALL_VALUES = RequestLoggerModule::ID . '_redact-all-values';

    public function __construct(
        private ConfigAccessSettingService $moduleSettingService
    ) {
    }

    public function getLogLevel(): string
    {
        return $this->moduleSettingService
            ->getStringSetting(self::SETTING_LOG_LEVEL, RequestLoggerModule::ID)
            ->getValue();
    }

    public function setLogLevel(string $value): string
    {
        return $this->moduleSettingService
            ->changeStringSetting(self::SETTING_LOG_LEVEL, $value, RequestLoggerModule::ID)
            ->getValue();
    }

    public function isLogFrontendEnabled(): bool
    {
        return $this->moduleSettingService
            ->getBooleanSetting(self::SETTING_LOG_FRONTEND, RequestLoggerModule::ID)
            ->getValue();
    }

    public function setLogFrontendEnabled(bool $value): bool
    {
        return $this->moduleSettingService
            ->changeBooleanSetting(self::SETTING_LOG_FRONTEND, $value, RequestLoggerModule::ID)
            ->getValue();
    }

    public function isLogAdminEnabled(): bool
    {
        return $this->moduleSettingService
            ->getBooleanSetting(self::SETTING_LOG_ADMIN, RequestLoggerModule::ID)
            ->getValue();
    }

    public function setLogAdminEnabled(bool $value): bool
    {
        return $this->moduleSettingService
            ->changeBooleanSetting(self::SETTING_LOG_ADMIN, $value, RequestLoggerModule::ID)
            ->getValue();
    }

    public function getRedactItems(): string
    {
        return $this->moduleSettingService
            ->getCollectionSetting(self::SETTING_REDACT, RequestLoggerModule::ID)
            ->getValue();
    }

    public function setRedactItems(string $jsonValue): string
    {
        // Additional validation: Ensure it's a list, not an object
        // This security check is specific to this module's requirements
        $items = json_decode($jsonValue, true);

        if (!is_array($items)) {
            throw new InvalidCollectionException('Invalid JSON array provided for redact items');
        }

        // Security: Ensure it's a sequential array (list), not an associative array (object)
        // This prevents prototype pollution and arbitrary key injection attacks
        if (!array_is_list($items)) {
            throw new InvalidCollectionException('Invalid JSON array provided for redact items - must be a list, not an object');
        }

        return $this->moduleSettingService
            ->changeCollectionSetting(self::SETTING_REDACT, $jsonValue, RequestLoggerModule::ID)
            ->getValue();
    }

    public function isRedactAllValuesEnabled(): bool
    {
        return $this->moduleSettingService
            ->getBooleanSetting(self::SETTING_REDACT_ALL_VALUES, RequestLoggerModule::ID)
            ->getValue();
    }

    public function setRedactAllValuesEnabled(bool $value): bool
    {
        return $this->moduleSettingService
            ->changeBooleanSetting(self::SETTING_REDACT_ALL_VALUES, $value, RequestLoggerModule::ID)
            ->getValue();
    }

    /**
     * @return SettingType[]
     */
    public function getAllSettings(): array
    {
        $configAccessSettings = $this->moduleSettingService->getSettingsList(RequestLoggerModule::ID);

        $settings = [];
        foreach ($configAccessSettings as $setting) {
            $settings[] = new SettingType($setting->getName(), $setting->getType());
        }

        return $settings;
    }
}
