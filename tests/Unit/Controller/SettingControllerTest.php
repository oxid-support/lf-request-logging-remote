<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller;

use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\BooleanSetting;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\SettingType;
use OxidEsales\GraphQL\ConfigurationAccess\Shared\DataType\StringSetting;
use OxidSupport\RequestLoggerRemote\Controller\SettingController;
use OxidSupport\RequestLoggerRemote\Service\SettingServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingController::class)]
final class SettingControllerTest extends TestCase
{
    public function testRequestLoggerSettingsReturnsAllSettings(): void
    {
        $expectedSettings = [
            new SettingType('oxsrequestlogger_log-level', 'select'),
            new SettingType('oxsrequestlogger_log-frontend', 'bool'),
        ];

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('getAllSettings')
            ->willReturn($expectedSettings);

        $result = $this->getSut(settingService: $settingService)->requestLoggerSettings();

        $this->assertSame($expectedSettings, $result);
    }

    public function testRequestLoggerLogLevelReturnsStringSetting(): void
    {
        $expectedSetting = new StringSetting('oxsrequestlogger_log-level', 'standard');

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('getLogLevel')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogLevel();

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerLogFrontendReturnsBooleanSetting(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_log-frontend', true);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('isLogFrontendEnabled')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogFrontend();

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerLogAdminReturnsBooleanSetting(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_log-admin', false);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('isLogAdminEnabled')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogAdmin();

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerRedactReturnsStringSetting(): void
    {
        $expectedSetting = new StringSetting('oxsrequestlogger_redact', '["password","secret"]');

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('getRedactItems')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerRedact();

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerRedactAllValuesReturnsBooleanSetting(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_redact-all-values', false);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('isRedactAllValuesEnabled')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerRedactAllValues();

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerLogLevelChangeCallsServiceAndReturnsResult(): void
    {
        $expectedSetting = new StringSetting('oxsrequestlogger_log-level', 'detailed');

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('setLogLevel')
            ->with('detailed')
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogLevelChange('detailed');

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerLogFrontendChangeCallsServiceAndReturnsResult(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_log-frontend', true);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('setLogFrontendEnabled')
            ->with(true)
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogFrontendChange(true);

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerLogAdminChangeCallsServiceAndReturnsResult(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_log-admin', false);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('setLogAdminEnabled')
            ->with(false)
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerLogAdminChange(false);

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerRedactChangeCallsServiceAndReturnsResult(): void
    {
        $jsonValue = '["password","token"]';
        $expectedSetting = new StringSetting('oxsrequestlogger_redact', $jsonValue);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('setRedactItems')
            ->with($jsonValue)
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerRedactChange($jsonValue);

        $this->assertSame($expectedSetting, $result);
    }

    public function testRequestLoggerRedactAllValuesChangeCallsServiceAndReturnsResult(): void
    {
        $expectedSetting = new BooleanSetting('oxsrequestlogger_redact-all-values', true);

        $settingService = $this->createMock(SettingServiceInterface::class);
        $settingService
            ->expects($this->once())
            ->method('setRedactAllValuesEnabled')
            ->with(true)
            ->willReturn($expectedSetting);

        $result = $this->getSut(settingService: $settingService)->requestLoggerRedactAllValuesChange(true);

        $this->assertSame($expectedSetting, $result);
    }

    private function getSut(
        ?SettingServiceInterface $settingService = null,
    ): SettingController {
        return new SettingController(
            settingService: $settingService ?? $this->createStub(SettingServiceInterface::class),
        );
    }
}
