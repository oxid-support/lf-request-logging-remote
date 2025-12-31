<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use OxidSupport\RequestLoggerRemote\Service\TokenGenerator;
use OxidSupport\RequestLoggerRemote\Service\TokenGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenGenerator::class)]
final class TokenGeneratorTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $generator = new TokenGenerator();

        $this->assertInstanceOf(TokenGeneratorInterface::class, $generator);
    }

    /**
     * This test requires OXID framework (Registry::getUtilsObject())
     * Note: This test documents that TokenGenerator still requires the OXID framework.
     * This is acceptable as it's a thin wrapper and can be easily mocked in tests.
     */
    public function testGenerateRequiresOxidFramework(): void
    {
        // TokenGenerator still uses Registry internally, which is OK as it's a thin wrapper
        // Controllers now depend on TokenGeneratorInterface which can be mocked
        $this->markTestSkipped('TokenGenerator requires OXID framework and is tested via integration tests');
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(TokenGenerator::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testHasGenerateMethod(): void
    {
        $reflection = new \ReflectionClass(TokenGenerator::class);

        $this->assertTrue($reflection->hasMethod('generate'));
    }

    public function testGenerateReturnsString(): void
    {
        $reflection = new \ReflectionClass(TokenGenerator::class);
        $method = $reflection->getMethod('generate');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
}
