<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Core;

use OxidSupport\RequestLoggerRemote\Core\Module;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Module::class)]
final class ModuleTest extends TestCase
{
    public function testModuleIdConstantIsCorrect(): void
    {
        $this->assertEquals('oxsrequestloggerremote', Module::MODULE_ID);
    }

    public function testModuleIdConstantIsString(): void
    {
        $this->assertIsString(Module::MODULE_ID);
    }

    public function testModuleIdConstantIsNotEmpty(): void
    {
        $this->assertNotEmpty(Module::MODULE_ID);
    }

    public function testSettingSetupTokenConstantIsCorrect(): void
    {
        $this->assertEquals('oxsrequestloggerremote_SetupToken', Module::SETTING_SETUP_TOKEN);
    }

    public function testSettingSetupTokenConstantIsString(): void
    {
        $this->assertIsString(Module::SETTING_SETUP_TOKEN);
    }

    public function testSettingSetupTokenConstantIsNotEmpty(): void
    {
        $this->assertNotEmpty(Module::SETTING_SETUP_TOKEN);
    }

    public function testSettingSetupTokenConstantStartsWithModuleId(): void
    {
        $this->assertStringStartsWith(Module::MODULE_ID, Module::SETTING_SETUP_TOKEN);
    }

    public function testApiUserEmailConstantIsCorrect(): void
    {
        $this->assertEquals('requestlogger-api@oxid-esales.com', Module::API_USER_EMAIL);
    }

    public function testApiUserEmailConstantIsString(): void
    {
        $this->assertIsString(Module::API_USER_EMAIL);
    }

    public function testApiUserEmailConstantIsNotEmpty(): void
    {
        $this->assertNotEmpty(Module::API_USER_EMAIL);
    }

    public function testApiUserEmailConstantIsValidEmailFormat(): void
    {
        $this->assertMatchesRegularExpression('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', Module::API_USER_EMAIL);
    }

    public function testApiUserEmailConstantContainsOxidDomain(): void
    {
        $this->assertStringContainsString('oxid-esales.com', Module::API_USER_EMAIL);
    }

    public function testModuleClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(Module::class);
        $this->assertTrue($reflection->isFinal());
    }

    public function testModuleClassHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(Module::class);
        $constructor = $reflection->getConstructor();

        // Class should have no explicit constructor (constants only)
        $this->assertNull($constructor);
    }

    public function testModuleClassHasExactlyThreeConstants(): void
    {
        $reflection = new \ReflectionClass(Module::class);
        $constants = $reflection->getConstants();

        $this->assertCount(3, $constants);
    }

    public function testAllConstantsArePublic(): void
    {
        $reflection = new \ReflectionClass(Module::class);
        $constants = $reflection->getReflectionConstants();

        foreach ($constants as $constant) {
            $this->assertTrue($constant->isPublic());
        }
    }
}
