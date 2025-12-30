<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Controller\PasswordController;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

#[CoversClass(PasswordController::class)]
final class PasswordControllerTest extends TestCase
{
    public function testThrowsInvalidTokenExceptionWhenTokenIsEmpty(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString(''));

        $this->expectException(InvalidTokenException::class);

        $this->getSut(moduleSettingService: $moduleSettingService)
            ->requestLoggerSetPassword('some-token', 'password123');
    }

    public function testThrowsInvalidTokenExceptionWhenTokenDoesNotMatch(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('stored-token'));

        $this->expectException(InvalidTokenException::class);

        $this->getSut(moduleSettingService: $moduleSettingService)
            ->requestLoggerSetPassword('wrong-token', 'password123');
    }

    public function testThrowsInvalidTokenExceptionWhenGetStringThrows(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willThrowException(new \RuntimeException('Setting not found'));

        $this->expectException(InvalidTokenException::class);

        $this->getSut(moduleSettingService: $moduleSettingService)
            ->requestLoggerSetPassword('some-token', 'password123');
    }

    public function testThrowsPasswordTooShortExceptionWhenPasswordUnder8Characters(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        $this->expectException(PasswordTooShortException::class);

        $this->getSut(moduleSettingService: $moduleSettingService)
            ->requestLoggerSetPassword('valid-token', 'short');
    }

    public function testThrowsPasswordTooShortExceptionWhenPasswordExactly7Characters(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        $this->expectException(PasswordTooShortException::class);

        $this->getSut(moduleSettingService: $moduleSettingService)
            ->requestLoggerSetPassword('valid-token', '1234567');
    }

    /**
     * Note: Tests for user loading and password setting cannot be fully unit tested
     * because they use oxNew(User::class) which requires the OXID framework.
     * Integration/acceptance tests should cover this functionality.
     *
     * This test documents that password validation passes for 8+ character passwords
     * before reaching the OXID framework dependency.
     */
    public function testAcceptsPasswordWithExactly8CharactersBeforeOxidFramework(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        // The test passes password validation (8 chars) but fails at oxNew
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('oxNew');

        $this->getSut(
            moduleSettingService: $moduleSettingService,
        )->requestLoggerSetPassword('valid-token', '12345678');
    }

    /**
     * This test documents that the set password mutation requires the OXID framework
     * for creating the User object and cannot be unit tested without it.
     */
    public function testSetPasswordRequiresOxidFrameworkForUserCreation(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('oxNew');

        $this->getSut(
            moduleSettingService: $moduleSettingService,
        )->requestLoggerSetPassword('valid-token', 'password123');
    }

    public function testResetPasswordRequiresOxidFramework(): void
    {
        // This test documents that the reset password mutation requires the OXID framework
        // and cannot be unit tested without it.

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('oxNew');

        $this->getSut()->requestLoggerResetPassword();
    }

    private function getSut(
        ?ApiUserServiceInterface $apiUserService = null,
        ?ModuleSettingServiceInterface $moduleSettingService = null,
    ): PasswordController {
        return new PasswordController(
            apiUserService: $apiUserService ?? $this->createStub(ApiUserServiceInterface::class),
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
