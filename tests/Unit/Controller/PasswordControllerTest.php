<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Controller\PasswordController;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordAlreadySetException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
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

    public function testThrowsPasswordAlreadySetExceptionWhenPasswordIsNotPlaceholder(): void
    {
        $moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingService
            ->expects($this->once())
            ->method('getString')
            ->with(Module::SETTING_SETUP_TOKEN, Module::MODULE_ID)
            ->willReturn(new UnicodeString('valid-token'));

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('$2y$10$someBcryptHash');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $this->expectException(PasswordAlreadySetException::class);

        $this->getSut(
            queryBuilderFactory: $queryBuilderFactory,
            moduleSettingService: $moduleSettingService,
        )->requestLoggerSetPassword('valid-token', 'password123');
    }

    /**
     * Note: The requestLoggerResetPassword mutation cannot be fully unit tested
     * because it uses oxNew(User::class) which requires the OXID framework.
     * Integration/acceptance tests should cover this functionality.
     */
    public function testResetPasswordRequiresOxidFramework(): void
    {
        // This test documents that the reset password mutation requires the OXID framework
        // and cannot be unit tested without it.

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('oxNew');

        $this->getSut()->requestLoggerResetPassword();
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null,
        ?ModuleSettingServiceInterface $moduleSettingService = null,
    ): PasswordController {
        return new PasswordController(
            queryBuilderFactory: $queryBuilderFactory ?? $this->createStub(QueryBuilderFactoryInterface::class),
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
