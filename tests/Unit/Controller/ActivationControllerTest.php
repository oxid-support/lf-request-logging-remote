<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller;

use OxidSupport\RequestLoggerRemote\Controller\ActivationController;
use OxidSupport\RequestLoggerRemote\Service\ActivationServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActivationController::class)]
final class ActivationControllerTest extends TestCase
{
    public function testRequestLoggerIsActiveReturnsTrue(): void
    {
        $activationService = $this->createMock(ActivationServiceInterface::class);
        $activationService
            ->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $result = $this->getSut(activationService: $activationService)->requestLoggerIsActive();

        $this->assertTrue($result);
    }

    public function testRequestLoggerIsActiveReturnsFalse(): void
    {
        $activationService = $this->createMock(ActivationServiceInterface::class);
        $activationService
            ->expects($this->once())
            ->method('isActive')
            ->willReturn(false);

        $result = $this->getSut(activationService: $activationService)->requestLoggerIsActive();

        $this->assertFalse($result);
    }

    public function testRequestLoggerActivateCallsServiceAndReturnsTrue(): void
    {
        $activationService = $this->createMock(ActivationServiceInterface::class);
        $activationService
            ->expects($this->once())
            ->method('activate')
            ->willReturn(true);

        $result = $this->getSut(activationService: $activationService)->requestLoggerActivate();

        $this->assertTrue($result);
    }

    public function testRequestLoggerDeactivateCallsServiceAndReturnsTrue(): void
    {
        $activationService = $this->createMock(ActivationServiceInterface::class);
        $activationService
            ->expects($this->once())
            ->method('deactivate')
            ->willReturn(true);

        $result = $this->getSut(activationService: $activationService)->requestLoggerDeactivate();

        $this->assertTrue($result);
    }

    private function getSut(
        ?ActivationServiceInterface $activationService = null,
    ): ActivationController {
        return new ActivationController(
            activationService: $activationService ?? $this->createStub(ActivationServiceInterface::class),
        );
    }
}
