<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\BooleanSetting;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\SettingType;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\StringSetting;

interface SettingServiceInterface
{
    public function getLogLevel(): StringSetting;

    public function setLogLevel(string $value): StringSetting;

    public function isLogFrontendEnabled(): BooleanSetting;

    public function setLogFrontendEnabled(bool $value): BooleanSetting;

    public function isLogAdminEnabled(): BooleanSetting;

    public function setLogAdminEnabled(bool $value): BooleanSetting;

    public function getRedactItems(): StringSetting;

    public function setRedactItems(string $jsonValue): StringSetting;

    public function isRedactAllValuesEnabled(): BooleanSetting;

    public function setRedactAllValuesEnabled(bool $value): BooleanSetting;

    /**
     * @return SettingType[]
     */
    public function getAllSettings(): array;
}
