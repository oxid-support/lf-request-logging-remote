<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use OxidEsales\GraphQL\ConfigurationAccess\Module\Service\ModuleSettingServiceInterface;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\BooleanSetting;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\StringSetting;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\SettingType as ConfigAccessSettingType;
use OxidSupport\RequestLoggerRemote\DataType\SettingType;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLoggerRemote\Exception\InvalidCollectionException;
use OxidSupport\RequestLoggerRemote\Service\SettingService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingService::class)]
final class SettingServiceTest extends TestCase
{
    private const SETTING_LOG_LEVEL = RequestLoggerModule::ID . '_log-level';
    private const SETTING_LOG_FRONTEND = RequestLoggerModule::ID . '_log-frontend';
    private const SETTING_LOG_ADMIN = RequestLoggerModule::ID . '_log-admin';
    private const SETTING_REDACT = RequestLoggerModule::ID . '_redact';
    private const SETTING_REDACT_ALL_VALUES = RequestLoggerModule::ID . '_redact-all-values';

    public function testGetLogLevelReturnsString(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getStringSetting')
            ->with(self::SETTING_LOG_LEVEL, RequestLoggerModule::ID)
            ->willReturn(new StringSetting(self::SETTING_LOG_LEVEL, 'standard'));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->getLogLevel();

        $this->assertSame('standard', $result);
    }

    public function testSetLogLevelSavesAndReturnsNewValue(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeStringSetting')
            ->with(self::SETTING_LOG_LEVEL, 'detailed', RequestLoggerModule::ID)
            ->willReturn(new StringSetting(self::SETTING_LOG_LEVEL, 'detailed'));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->setLogLevel('detailed');

        $this->assertSame('detailed', $result);
    }

    public function testIsLogFrontendEnabledReturnsBool(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getBooleanSetting')
            ->with(self::SETTING_LOG_FRONTEND, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_LOG_FRONTEND, true));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->isLogFrontendEnabled();

        $this->assertTrue($result);
    }

    public function testSetLogFrontendEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeBooleanSetting')
            ->with(self::SETTING_LOG_FRONTEND, false, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_LOG_FRONTEND, false));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->setLogFrontendEnabled(false);

        $this->assertFalse($result);
    }

    public function testIsLogAdminEnabledReturnsBool(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getBooleanSetting')
            ->with(self::SETTING_LOG_ADMIN, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_LOG_ADMIN, false));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->isLogAdminEnabled();

        $this->assertFalse($result);
    }

    public function testSetLogAdminEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeBooleanSetting')
            ->with(self::SETTING_LOG_ADMIN, true, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_LOG_ADMIN, true));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->setLogAdminEnabled(true);

        $this->assertTrue($result);
    }

    public function testGetRedactItemsReturnsJsonEncodedString(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getCollectionSetting')
            ->with(self::SETTING_REDACT, RequestLoggerModule::ID)
            ->willReturn(new StringSetting(self::SETTING_REDACT, '["password","secret","token"]'));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->getRedactItems();

        $this->assertSame('["password","secret","token"]', $result);
    }

    public function testSetRedactItemsDecodesJsonAndSaves(): void
    {
        $jsonValue = '["password","token"]';

        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeCollectionSetting')
            ->with(self::SETTING_REDACT, $jsonValue, RequestLoggerModule::ID)
            ->willReturn(new StringSetting(self::SETTING_REDACT, $jsonValue));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->setRedactItems($jsonValue);

        $this->assertSame($jsonValue, $result);
    }

    public function testSetRedactItemsThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage('Invalid JSON array provided for redact items');

        $this->getSut()->setRedactItems('not valid json');
    }

    public function testSetRedactItemsRejectsAssociativeArray(): void
    {
        // JSON objects with non-numeric keys decode to associative arrays in PHP,
        // which should be rejected for security reasons (prevents prototype pollution attacks)
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage('must be a list, not an object');

        // Create a mock that won't be called (exception should be thrown before)
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->never())
            ->method('changeCollectionSetting');

        $service = new SettingService($moduleSettingService);
        // Use non-numeric keys to ensure it's a real associative array
        $service->setRedactItems('{"key": "password", "other": "token"}');
    }

    public function testSetRedactItemsAcceptsObjectWithNumericStringKeys(): void
    {
        // JSON objects with numeric string keys (e.g., "0", "1") are converted
        // to PHP arrays with integer keys, which passes array_is_list()
        $jsonValue = '{"0": "password", "1": "token"}';

        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeCollectionSetting')
            ->willReturn(new StringSetting(self::SETTING_REDACT, '["password","token"]'));

        $result = (new SettingService($moduleSettingService))->setRedactItems($jsonValue);
        $this->assertIsString($result);
    }

    public function testSetRedactItemsThrowsExceptionForJsonString(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage('Invalid JSON array provided for redact items');

        $this->getSut()->setRedactItems('"just a string"');
    }

    public function testIsRedactAllValuesEnabledReturnsBool(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getBooleanSetting')
            ->with(self::SETTING_REDACT_ALL_VALUES, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_REDACT_ALL_VALUES, false));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->isRedactAllValuesEnabled();

        $this->assertFalse($result);
    }

    public function testSetRedactAllValuesEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('changeBooleanSetting')
            ->with(self::SETTING_REDACT_ALL_VALUES, true, RequestLoggerModule::ID)
            ->willReturn(new BooleanSetting(self::SETTING_REDACT_ALL_VALUES, true));

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->setRedactAllValuesEnabled(true);

        $this->assertTrue($result);
    }

    public function testGetAllSettingsReturnsAllSettingTypes(): void
    {
        $configAccessSettings = [
            new ConfigAccessSettingType(self::SETTING_LOG_LEVEL, 'select'),
            new ConfigAccessSettingType(self::SETTING_LOG_FRONTEND, 'bool'),
            new ConfigAccessSettingType(self::SETTING_LOG_ADMIN, 'bool'),
            new ConfigAccessSettingType(self::SETTING_REDACT, 'arr'),
            new ConfigAccessSettingType(self::SETTING_REDACT_ALL_VALUES, 'bool'),
        ];

        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getSettingsList')
            ->with(RequestLoggerModule::ID)
            ->willReturn($configAccessSettings);

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->getAllSettings();

        $this->assertCount(5, $result);
        $this->assertContainsOnlyInstancesOf(SettingType::class, $result);

        $names = array_map(fn (SettingType $s) => $s->getName(), $result);

        $this->assertContains(self::SETTING_LOG_LEVEL, $names);
        $this->assertContains(self::SETTING_LOG_FRONTEND, $names);
        $this->assertContains(self::SETTING_LOG_ADMIN, $names);
        $this->assertContains(self::SETTING_REDACT, $names);
        $this->assertContains(self::SETTING_REDACT_ALL_VALUES, $names);
    }

    public function testGetAllSettingsReturnsCorrectTypes(): void
    {
        $configAccessSettings = [
            new ConfigAccessSettingType(self::SETTING_LOG_LEVEL, 'select'),
            new ConfigAccessSettingType(self::SETTING_LOG_FRONTEND, 'bool'),
            new ConfigAccessSettingType(self::SETTING_LOG_ADMIN, 'bool'),
            new ConfigAccessSettingType(self::SETTING_REDACT, 'arr'),
            new ConfigAccessSettingType(self::SETTING_REDACT_ALL_VALUES, 'bool'),
        ];

        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getSettingsList')
            ->with(RequestLoggerModule::ID)
            ->willReturn($configAccessSettings);

        $result = $this->getSut(moduleSettingService: $moduleSettingService)->getAllSettings();

        $settingsByName = [];
        foreach ($result as $setting) {
            $settingsByName[$setting->getName()] = $setting->getType();
        }

        $this->assertSame('select', $settingsByName[self::SETTING_LOG_LEVEL]);
        $this->assertSame('bool', $settingsByName[self::SETTING_LOG_FRONTEND]);
        $this->assertSame('bool', $settingsByName[self::SETTING_LOG_ADMIN]);
        $this->assertSame('arr', $settingsByName[self::SETTING_REDACT]);
        $this->assertSame('bool', $settingsByName[self::SETTING_REDACT_ALL_VALUES]);
    }

    private function getSut(
        ?ModuleSettingServiceInterface $moduleSettingService = null,
    ): SettingService {
        return new SettingService(
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
