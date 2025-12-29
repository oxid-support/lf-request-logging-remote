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
        $shopId = 1;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('activate')
            ->with(RequestLoggerModule::ID, $shopId);

        $result = $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->activate();

        $this->assertTrue($result);
    }

    public function testActivateThrowsModuleActivationExceptionOnError(): void
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
            ->method('activate')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willThrowException(new Exception('Activation failed'));

        $this->expectException(ModuleActivationException::class);
        $this->expectExceptionMessage('Failed to activate module: Activation failed');

        $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->activate();
    }

    public function testDeactivateReturnsTrue(): void
    {
        $shopId = 2;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('deactivate')
            ->with(RequestLoggerModule::ID, $shopId);

        $result = $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->deactivate();

        $this->assertTrue($result);
    }

    public function testDeactivateThrowsModuleDeactivationExceptionOnError(): void
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
            ->method('deactivate')
            ->with(RequestLoggerModule::ID, $shopId)
            ->willThrowException(new Exception('Deactivation failed'));

        $this->expectException(ModuleDeactivationException::class);
        $this->expectExceptionMessage('Failed to deactivate module: Deactivation failed');

        $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
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

    public function testActivateUsesCorrectShopId(): void
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
            ->method('activate')
            ->with(RequestLoggerModule::ID, $shopId);

        $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->activate();
    }

    public function testDeactivateUsesCorrectShopId(): void
    {
        $shopId = 3;

        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getCurrentShopId')
            ->willReturn($shopId);

        $moduleActivationBridge = $this->createMock(ModuleActivationBridgeInterface::class);
        $moduleActivationBridge
            ->expects($this->once())
            ->method('deactivate')
            ->with(RequestLoggerModule::ID, $shopId);

        $this->getSut(
            context: $context,
            moduleActivationBridge: $moduleActivationBridge,
        )->deactivate();
    }

    private function getSut(
        ?ContextInterface $context = null,
        ?ModuleActivationBridgeInterface $moduleActivationBridge = null,
    ): ActivationService {
        return new ActivationService(
            context: $context ?? $this->createStub(ContextInterface::class),
            moduleActivationBridge: $moduleActivationBridge ?? $this->createStub(ModuleActivationBridgeInterface::class),
        );
    }
}
