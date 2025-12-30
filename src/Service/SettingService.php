<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidSupport\RequestLoggerRemote\DataType\SettingType;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLogger\Shop\Compatibility\ModuleSettings\ModuleSettingsPort;
use OxidSupport\RequestLoggerRemote\Exception\InvalidCollectionException;

final readonly class SettingService implements SettingServiceInterface
{
    private const SETTING_LOG_LEVEL = RequestLoggerModule::ID . '_log-level';
    private const SETTING_LOG_FRONTEND = RequestLoggerModule::ID . '_log-frontend';
    private const SETTING_LOG_ADMIN = RequestLoggerModule::ID . '_log-admin';
    private const SETTING_REDACT = RequestLoggerModule::ID . '_redact';
    private const SETTING_REDACT_ALL_VALUES = RequestLoggerModule::ID . '_redact-all-values';

    private const SETTINGS = [
        self::SETTING_LOG_LEVEL => 'select',
        self::SETTING_LOG_FRONTEND => 'bool',
        self::SETTING_LOG_ADMIN => 'bool',
        self::SETTING_REDACT => 'arr',
        self::SETTING_REDACT_ALL_VALUES => 'bool',
    ];

    public function __construct(
        private ModuleSettingsPort $moduleSettingsPort
    ) {
    }

    public function getLogLevel(): string
    {
        return $this->moduleSettingsPort->getString(self::SETTING_LOG_LEVEL, RequestLoggerModule::ID);
    }

    public function setLogLevel(string $value): string
    {
        $this->moduleSettingsPort->saveString(self::SETTING_LOG_LEVEL, $value, RequestLoggerModule::ID);

        return $this->getLogLevel();
    }

    public function isLogFrontendEnabled(): bool
    {
        return $this->moduleSettingsPort->getBoolean(self::SETTING_LOG_FRONTEND, RequestLoggerModule::ID);
    }

    public function setLogFrontendEnabled(bool $value): bool
    {
        $this->moduleSettingsPort->saveBoolean(self::SETTING_LOG_FRONTEND, $value, RequestLoggerModule::ID);

        return $this->isLogFrontendEnabled();
    }

    public function isLogAdminEnabled(): bool
    {
        return $this->moduleSettingsPort->getBoolean(self::SETTING_LOG_ADMIN, RequestLoggerModule::ID);
    }

    public function setLogAdminEnabled(bool $value): bool
    {
        $this->moduleSettingsPort->saveBoolean(self::SETTING_LOG_ADMIN, $value, RequestLoggerModule::ID);

        return $this->isLogAdminEnabled();
    }

    public function getRedactItems(): string
    {
        $items = $this->moduleSettingsPort->getCollection(self::SETTING_REDACT, RequestLoggerModule::ID);

        return json_encode($items, JSON_THROW_ON_ERROR);
    }

    public function setRedactItems(string $jsonValue): string
    {
        $items = json_decode($jsonValue, true);

        if (!is_array($items)) {
            throw new InvalidCollectionException('Invalid JSON array provided for redact items');
        }

        $this->moduleSettingsPort->saveCollection(self::SETTING_REDACT, $items, RequestLoggerModule::ID);

        return $this->getRedactItems();
    }

    public function isRedactAllValuesEnabled(): bool
    {
        return $this->moduleSettingsPort->getBoolean(self::SETTING_REDACT_ALL_VALUES, RequestLoggerModule::ID);
    }

    public function setRedactAllValuesEnabled(bool $value): bool
    {
        $this->moduleSettingsPort->saveBoolean(self::SETTING_REDACT_ALL_VALUES, $value, RequestLoggerModule::ID);

        return $this->isRedactAllValuesEnabled();
    }

    /**
     * @return SettingType[]
     */
    public function getAllSettings(): array
    {
        $settings = [];

        foreach (self::SETTINGS as $name => $type) {
            $settings[] = new SettingType($name, $type);
        }

        return $settings;
    }
}
