<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Framework;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;
use OxidSupport\RequestLoggerRemote\Framework\NamespaceMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamespaceMapper::class)]
final class NamespaceMapperTest extends TestCase
{
    public function testImplementsNamespaceMapperInterface(): void
    {
        $mapper = new NamespaceMapper();

        $this->assertInstanceOf(NamespaceMapperInterface::class, $mapper);
    }

    public function testGetControllerNamespaceMappingReturnsCorrectNamespace(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getControllerNamespaceMapping();

        $this->assertIsArray($mapping);
        $this->assertArrayHasKey('OxidSupport\\RequestLoggerRemote\\Controller', $mapping);
    }

    public function testGetControllerNamespaceMappingReturnsValidPath(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getControllerNamespaceMapping();

        $path = $mapping['OxidSupport\\RequestLoggerRemote\\Controller'];

        $this->assertIsString($path);
        $this->assertStringEndsWith('/Controller/', $path);
    }

    public function testGetControllerNamespaceMappingPathExists(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getControllerNamespaceMapping();

        $path = $mapping['OxidSupport\\RequestLoggerRemote\\Controller'];

        // The path contains relative path from NamespaceMapper (__DIR__ . '/../Controller/')
        // Normalize the path by resolving it
        $this->assertDirectoryExists($path, 'Controller directory should exist');
        $this->assertStringEndsWith('/Controller/', $path);
    }

    public function testGetTypeNamespaceMappingReturnsCorrectNamespace(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getTypeNamespaceMapping();

        $this->assertIsArray($mapping);
        $this->assertArrayHasKey('OxidSupport\\RequestLoggerRemote\\DataType', $mapping);
    }

    public function testGetTypeNamespaceMappingReturnsValidPath(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getTypeNamespaceMapping();

        $path = $mapping['OxidSupport\\RequestLoggerRemote\\DataType'];

        $this->assertIsString($path);
        $this->assertStringEndsWith('/DataType/', $path);
    }

    public function testGetTypeNamespaceMappingPathExists(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getTypeNamespaceMapping();

        $path = $mapping['OxidSupport\\RequestLoggerRemote\\DataType'];

        // The path contains relative path from NamespaceMapper (__DIR__ . '/../DataType/')
        // Normalize the path by resolving it
        $this->assertDirectoryExists($path, 'DataType directory should exist');
        $this->assertStringEndsWith('/DataType/', $path);
    }

    public function testControllerMappingDoesNotIncludeAdminSubdirectory(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getControllerNamespaceMapping();

        // Should only map base Controller namespace, not Admin subdirectory
        $this->assertArrayNotHasKey('OxidSupport\\RequestLoggerRemote\\Controller\\Admin', $mapping);
    }

    public function testReturnsOnlyOneControllerNamespace(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getControllerNamespaceMapping();

        $this->assertCount(1, $mapping);
    }

    public function testReturnsOnlyOneTypeNamespace(): void
    {
        $mapper = new NamespaceMapper();
        $mapping = $mapper->getTypeNamespaceMapping();

        $this->assertCount(1, $mapping);
    }
}
