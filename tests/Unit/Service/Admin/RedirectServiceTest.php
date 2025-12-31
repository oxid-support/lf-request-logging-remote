<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service\Admin;

use OxidSupport\RequestLoggerRemote\Service\Admin\RedirectService;
use OxidSupport\RequestLoggerRemote\Service\Admin\RedirectServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RedirectService::class)]
final class RedirectServiceTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $service = new RedirectService();

        $this->assertInstanceOf(RedirectServiceInterface::class, $service);
    }

    /**
     * This test requires OXID framework (Registry)
     */
    public function testRedirectToModuleConfigRequiresOxidFramework(): void
    {
        $this->expectException(\Error::class);

        $service = new RedirectService();
        $service->redirectToModuleConfig();
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(RedirectService::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testHasRedirectToModuleConfigMethod(): void
    {
        $reflection = new \ReflectionClass(RedirectService::class);

        $this->assertTrue($reflection->hasMethod('redirectToModuleConfig'));
    }

    public function testRedirectToModuleConfigAcceptsArray(): void
    {
        $reflection = new \ReflectionClass(RedirectService::class);
        $method = $reflection->getMethod('redirectToModuleConfig');

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    public function testRedirectToModuleConfigReturnsVoid(): void
    {
        $reflection = new \ReflectionClass(RedirectService::class);
        $method = $reflection->getMethod('redirectToModuleConfig');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }
}
