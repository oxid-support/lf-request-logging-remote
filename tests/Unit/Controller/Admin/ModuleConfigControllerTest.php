<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller\Admin;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSupport\RequestLoggerRemote\Controller\Admin\ModuleConfigController;
use OxidSupport\RequestLoggerRemote\Core\Module;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModuleConfigController::class)]
final class ModuleConfigControllerTest extends TestCase
{
    /**
     * This test documents that ModuleConfigController requires the OXID framework
     * for several dependencies that cannot be easily mocked in a pure unit test:
     *
     * 1. ContainerFactory::getInstance() - static factory method
     * 2. ContextInterface - requires shop context
     * 3. ShopConfigurationDaoInterface - requires database access
     * 4. SetupStatusServiceInterface - requires migrations table access
     * 5. Extends ModuleConfiguration - OXID admin base class
     * 6. getEditObjectId() - requires admin request context
     *
     * These dependencies make the controller difficult to unit test without the full OXID framework.
     * Integration or acceptance tests should cover this functionality.
     */
    public function testIsModuleActivatedRequiresOxidFramework(): void
    {
        $this->expectException(\Error::class);

        $controller = new ModuleConfigController();
        $controller->isModuleActivated();
    }

    public function testIsMigrationExecutedRequiresOxidFramework(): void
    {
        $this->expectException(\Error::class);

        $controller = new ModuleConfigController();
        $controller->isMigrationExecuted();
    }

    /**
     * This test documents the expected behavior of isModuleActivated():
     *
     * 1. Checks if current module ID matches MODULE_ID
     * 2. Retrieves shop configuration from DAO
     * 3. Returns activation status of the module
     * 4. Returns false if module ID doesn't match
     * 5. Returns false if any exception occurs
     *
     * This behavior should be verified in integration/acceptance tests.
     */
    public function testIsModuleActivatedExpectedBehaviorDocumentation(): void
    {
        $this->assertTrue(
            method_exists(ModuleConfigController::class, 'isModuleActivated'),
            'ModuleConfigController should have isModuleActivated method'
        );
    }

    /**
     * This test documents the expected behavior of isMigrationExecuted():
     *
     * 1. Checks if current module ID matches MODULE_ID
     * 2. Retrieves SetupStatusService from container
     * 3. Returns migration execution status
     * 4. Returns true if module ID doesn't match (no check needed)
     * 5. Returns false if any exception occurs
     *
     * This behavior should be verified in integration/acceptance tests.
     */
    public function testIsMigrationExecutedExpectedBehaviorDocumentation(): void
    {
        $this->assertTrue(
            method_exists(ModuleConfigController::class, 'isMigrationExecuted'),
            'ModuleConfigController should have isMigrationExecuted method'
        );
    }

    public function testIsModuleActivatedReturnsBoolean(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $method = $reflection->getMethod('isModuleActivated');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType, 'isModuleActivated should have return type');
        $this->assertEquals('bool', $returnType->getName(), 'isModuleActivated should return bool');
    }

    public function testIsMigrationExecutedReturnsBoolean(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $method = $reflection->getMethod('isMigrationExecuted');

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType, 'isMigrationExecuted should have return type');
        $this->assertEquals('bool', $returnType->getName(), 'isMigrationExecuted should return bool');
    }

    public function testIsModuleActivatedIsPublic(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $method = $reflection->getMethod('isModuleActivated');

        $this->assertTrue($method->isPublic(), 'isModuleActivated should be public');
    }

    public function testIsMigrationExecutedIsPublic(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $method = $reflection->getMethod('isMigrationExecuted');

        $this->assertTrue($method->isPublic(), 'isMigrationExecuted should be public');
    }

    public function testExtendsModuleConfiguration(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $parent = $reflection->getParentClass();

        $this->assertNotFalse($parent, 'Should extend a parent class');
        $this->assertEquals(
            'OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration',
            $parent->getName(),
            'Should extend ModuleConfiguration'
        );
    }

    public function testClassIsNotFinal(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);

        // ModuleConfigController is not final because OXID may need to extend it
        $this->assertFalse($reflection->isFinal(), 'ModuleConfigController should not be final');
    }

    public function testHasNoConstructor(): void
    {
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $constructor = $reflection->getConstructor();

        // If constructor exists, it should be inherited from parent
        if ($constructor !== null) {
            $this->assertNotEquals(
                ModuleConfigController::class,
                $constructor->getDeclaringClass()->getName(),
                'Should not have own constructor (uses parent constructor)'
            );
        } else {
            $this->assertTrue(true, 'No constructor defined');
        }
    }

    public function testUsesModuleConstant(): void
    {
        // Verify the controller uses Module::MODULE_ID constant
        $reflection = new \ReflectionClass(ModuleConfigController::class);
        $method = $reflection->getMethod('isModuleActivated');

        // Get method source to check if it uses Module::MODULE_ID
        $filename = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        $this->assertNotFalse($filename, 'Method should have a file');

        $source = file($filename);
        $methodSource = implode('', array_slice($source, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString(
            'Module::MODULE_ID',
            $methodSource,
            'isModuleActivated should use Module::MODULE_ID constant'
        );
    }
}
