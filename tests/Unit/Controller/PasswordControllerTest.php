<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Controller\PasswordController;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Exception\UserNotFoundException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use OxidSupport\RequestLoggerRemote\Service\TokenGeneratorInterface;
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
     * After refactoring: setPasswordForApiUser() is now in ApiUserService,
     * which uses oxNew internally. The controller delegates to the service.
     */
    public function testSetPasswordDelegatesToService(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        $apiUserService = $this->createMock(ApiUserServiceInterface::class);
        $apiUserService
            ->expects($this->once())
            ->method('setPasswordForApiUser')
            ->with('password123');

        $moduleSettingService
            ->expects($this->once())
            ->method('saveString')
            ->with(Module::SETTING_SETUP_TOKEN, '', Module::MODULE_ID);

        $result = $this->getSut(
            apiUserService: $apiUserService,
            moduleSettingService: $moduleSettingService
        )->requestLoggerSetPassword('valid-token', 'password123');

        $this->assertTrue($result);
    }

    /**
     * After refactoring: resetPasswordForApiUser() is now in ApiUserService,
     * token generation is in TokenGeneratorInterface. Controller only orchestrates.
     */
    public function testResetPasswordDelegatesToServices(): void
    {
        $tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $tokenGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('generated-token-123');

        $apiUserService = $this->createMock(ApiUserServiceInterface::class);
        $apiUserService
            ->expects($this->once())
            ->method('resetPasswordForApiUser');

        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('saveString')
            ->with(Module::SETTING_SETUP_TOKEN, 'generated-token-123', Module::MODULE_ID);

        $result = $this->getSut(
            apiUserService: $apiUserService,
            moduleSettingService: $moduleSettingService,
            tokenGenerator: $tokenGenerator
        )->requestLoggerResetPassword();

        $this->assertEquals('generated-token-123', $result);
    }

    public function testThrowsUserNotFoundExceptionWhenServiceThrows(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->method('getString')
            ->willReturn(new UnicodeString('valid-token'));

        $apiUserService = $this->createMock(ApiUserServiceInterface::class);
        $apiUserService
            ->method('setPasswordForApiUser')
            ->willThrowException(new UserNotFoundException());

        $this->expectException(UserNotFoundException::class);

        $this->getSut(
            apiUserService: $apiUserService,
            moduleSettingService: $moduleSettingService
        )->requestLoggerSetPassword('valid-token', 'password123');
    }

    private function getSut(
        ?ApiUserServiceInterface $apiUserService = null,
        ?ModuleSettingServiceInterface $moduleSettingService = null,
        ?TokenGeneratorInterface $tokenGenerator = null,
    ): PasswordController {
        return new PasswordController(
            apiUserService: $apiUserService ?? $this->createStub(ApiUserServiceInterface::class),
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
            tokenGenerator: $tokenGenerator ?? $this->createStub(TokenGeneratorInterface::class),
        );
    }
}
