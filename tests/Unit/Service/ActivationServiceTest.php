<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use Exception;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\GraphQL\ConfigurationAccess\Module\Service\ModuleActivationServiceInterface as ConfigAccessActivationService;
use OxidSupport\RequestLogger\Module\Module as RequestLoggerModule;
use OxidSupport\RequestLoggerRemote\Exception\ModuleActivationException;
use OxidSupport\RequestLoggerRemote\Exception\ModuleDeactivationException;
use OxidSupport\RequestLoggerRemote\Service\ActivationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivationService::class)]
final class ActivationServiceTest extends TestCase
{
    public function testActivateReturnsTrue(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('activateModule')
            ->with(RequestLoggerModule::ID)
            ->willReturn(true);

        $result = $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->activate();

        $this->assertTrue($result);
    }

    public function testActivateThrowsModuleActivationExceptionOnError(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('activateModule')
            ->with(RequestLoggerModule::ID)
            ->willThrowException(new Exception('Activation failed'));

        $this->expectException(ModuleActivationException::class);
        // Security: Internal error details should not be exposed in exception message
        $this->expectExceptionMessage('Failed to activate module');

        $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->activate();
    }

    public function testDeactivateReturnsTrue(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('deactivateModule')
            ->with(RequestLoggerModule::ID)
            ->willReturn(true);

        $result = $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->deactivate();

        $this->assertTrue($result);
    }

    public function testDeactivateThrowsModuleDeactivationExceptionOnError(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('deactivateModule')
            ->with(RequestLoggerModule::ID)
            ->willThrowException(new Exception('Deactivation failed'));

        $this->expectException(ModuleDeactivationException::class);
        // Security: Internal error details should not be exposed in exception message
        $this->expectExceptionMessage('Failed to deactivate module');

        $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->deactivate();
    }

    public function testIsActiveReturnsTrue(): void
    {
        $shopId = 1;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('isActive')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willReturn(true);

        $result = $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->isActive();

        $this->assertTrue($result);
    }

    public function testIsActiveReturnsFalse(): void
    {
        $shopId = 1;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('isActive')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willReturn(false);

        $result = $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->isActive();

        $this->assertFalse($result);
    }

    public function testIsActiveReturnsFalseOnException(): void
    {
        $shopId = 1;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('isActive')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willThrowException(new Exception('Module not found'));

        $result = $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->isActive();

        $this->assertFalse($result);
    }

    public function testIsActiveUsesCorrectShopId(): void
    {
        $shopId = 5;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('isActive')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willReturn(true);

        $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->isActive();
    }

    public function testActivateDelegatesToConfigAccessService(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('activateModule')
            ->with(RequestLoggerModule::ID)
            ->willReturn(true);

        $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->activate();
    }

    public function testDeactivateDelegatesToConfigAccessService(): void
    {
        $configAccessActivationService = $this->createMock(ConfigAccessActivationService::class);
        $configAccessActivationService
            ->expects($this->once())
            ->method('deactivateModule')
            ->with(RequestLoggerModule::ID)
            ->willReturn(true);

        $this->getSut(
            configAccessActivationService: $configAccessActivationService,
        )->deactivate();
    }

    private function getSut(
        ?ContextInterface $context = null,
        ?ModuleActivationBridgeInterface $moduleActivationBridge = null,
        ?ConfigAccessActivationService $configAccessActivationService = null,
    ): ActivationService {
        return new ActivationService(
            context: $context ?? $this->createStub(ContextInterface::class),
            moduleActivationBridge: $moduleActivationBridge ?? $this->createStub(ModuleActivationBridgeInterface::class),
            configAccessActivationService: $configAccessActivationService ?? $this->createStub(ConfigAccessActivationService::class),
        );
    }
}
