<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use OxidSupport\RequestLoggerRemote\DataType\SettingType;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLogger\Shop\Compatibility\ModuleSettings\ModuleSettingsPort;
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
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('getString')
            ->with(self::SETTING_LOG_LEVEL, RequestLoggerModule::ID)
            ->willReturn('standard');

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->getLogLevel();

        $this->assertSame('standard', $result);
    }

    public function testSetLogLevelSavesAndReturnsNewValue(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveString')
            ->with(self::SETTING_LOG_LEVEL, 'detailed', RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getString')
            ->with(self::SETTING_LOG_LEVEL, RequestLoggerModule::ID)
            ->willReturn('detailed');

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setLogLevel('detailed');

        $this->assertSame('detailed', $result);
    }

    public function testIsLogFrontendEnabledReturnsBool(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_LOG_FRONTEND, RequestLoggerModule::ID)
            ->willReturn(true);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->isLogFrontendEnabled();

        $this->assertTrue($result);
    }

    public function testSetLogFrontendEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveBoolean')
            ->with(self::SETTING_LOG_FRONTEND, false, RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_LOG_FRONTEND, RequestLoggerModule::ID)
            ->willReturn(false);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setLogFrontendEnabled(false);

        $this->assertFalse($result);
    }

    public function testIsLogAdminEnabledReturnsBool(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_LOG_ADMIN, RequestLoggerModule::ID)
            ->willReturn(false);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->isLogAdminEnabled();

        $this->assertFalse($result);
    }

    public function testSetLogAdminEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveBoolean')
            ->with(self::SETTING_LOG_ADMIN, true, RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_LOG_ADMIN, RequestLoggerModule::ID)
            ->willReturn(true);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setLogAdminEnabled(true);

        $this->assertTrue($result);
    }

    public function testGetRedactItemsReturnsJsonEncodedString(): void
    {
        $items = ['password', 'secret', 'token'];

        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('getCollection')
            ->with(self::SETTING_REDACT, RequestLoggerModule::ID)
            ->willReturn($items);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->getRedactItems();

        $this->assertSame('["password","secret","token"]', $result);
    }

    public function testSetRedactItemsDecodesJsonAndSaves(): void
    {
        $jsonValue = '["password","token"]';
        $expectedArray = ['password', 'token'];

        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveCollection')
            ->with(self::SETTING_REDACT, $expectedArray, RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getCollection')
            ->with(self::SETTING_REDACT, RequestLoggerModule::ID)
            ->willReturn($expectedArray);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setRedactItems($jsonValue);

        $this->assertSame($jsonValue, $result);
    }

    public function testSetRedactItemsThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage('Invalid JSON array provided for redact items');

        $this->getSut()->setRedactItems('not valid json');
    }

    public function testSetRedactItemsAcceptsAssociativeArray(): void
    {
        // JSON objects decode to associative arrays in PHP, which are valid arrays
        $jsonValue = '{"0": "password", "1": "token"}';
        $expectedArray = ['0' => 'password', '1' => 'token'];

        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveCollection')
            ->with(self::SETTING_REDACT, $expectedArray, RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getCollection')
            ->with(self::SETTING_REDACT, RequestLoggerModule::ID)
            ->willReturn($expectedArray);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setRedactItems($jsonValue);

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
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_REDACT_ALL_VALUES, RequestLoggerModule::ID)
            ->willReturn(false);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->isRedactAllValuesEnabled();

        $this->assertFalse($result);
    }

    public function testSetRedactAllValuesEnabledSavesAndReturnsNewValue(): void
    {
        $moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $moduleSettingsPort
            ->expects($this->once())
            ->method('saveBoolean')
            ->with(self::SETTING_REDACT_ALL_VALUES, true, RequestLoggerModule::ID);

        $moduleSettingsPort
            ->expects($this->once())
            ->method('getBoolean')
            ->with(self::SETTING_REDACT_ALL_VALUES, RequestLoggerModule::ID)
            ->willReturn(true);

        $result = $this->getSut(moduleSettingsPort: $moduleSettingsPort)->setRedactAllValuesEnabled(true);

        $this->assertTrue($result);
    }

    public function testGetAllSettingsReturnsAllSettingTypes(): void
    {
        $result = $this->getSut()->getAllSettings();

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
        $result = $this->getSut()->getAllSettings();

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
        ?ModuleSettingsPort $moduleSettingsPort = null,
    ): SettingService {
        return new SettingService(
            moduleSettingsPort: $moduleSettingsPort ?? $this->createStub(ModuleSettingsPort::class),
        );
    }
}
