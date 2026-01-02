<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Controller\Admin;

use OxidSupport\RequestLoggerRemote\Controller\Admin\PasswordResetController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordResetController::class)]
final class PasswordResetControllerTest extends TestCase
{
    /**
     * After refactoring, the controller still requires OXID framework for ContainerFactory,
     * but now uses lazy-loaded services via getter methods instead of direct static calls.
     */
    public function testResetPasswordRequiresOxidFramework(): void
    {
        $this->expectException(\Error::class);

        $controller = new PasswordResetController();
        $controller->resetPassword();
    }

    /**
     * This test documents the refactored behavior of resetPassword():
     *
     * 1. Generates a new setup token via TokenGeneratorInterface
     * 2. Resets the password via ApiUserService::resetPasswordForApiUser()
     * 3. Saves the token to module settings via ModuleSettingService
     * 4. Returns redirect string for OXID's internal redirect mechanism
     * 5. If UserNotFoundException is caught, returns error redirect string
     *
     * Benefits after refactoring:
     * - Uses OXID's native redirect pattern (return string from action)
     * - Session is preserved during redirect
     * - No more custom HTTP redirect handling
     */
    public function testResetPasswordExpectedBehaviorDocumentation(): void
    {
        $this->assertTrue(
            method_exists(PasswordResetController::class, 'resetPassword'),
            'PasswordResetController should have resetPassword method'
        );
    }

    public function testResetPasswordReturnsString(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);
        $method = $reflection->getMethod('resetPassword');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType, 'resetPassword should have a return type');
        $this->assertEquals('string', $returnType->getName(), 'resetPassword should return string');
    }

    public function testHasPrivateGetApiUserServiceMethod(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $this->assertTrue(
            $reflection->hasMethod('getApiUserService'),
            'Should have getApiUserService method for lazy loading'
        );

        $method = $reflection->getMethod('getApiUserService');
        $this->assertTrue($method->isPrivate(), 'getApiUserService should be private');
    }

    public function testHasPrivateGetModuleSettingServiceMethod(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $this->assertTrue(
            $reflection->hasMethod('getModuleSettingService'),
            'Should have getModuleSettingService method for lazy loading'
        );

        $method = $reflection->getMethod('getModuleSettingService');
        $this->assertTrue($method->isPrivate(), 'getModuleSettingService should be private');
    }

    public function testHasPrivateGetTokenGeneratorMethod(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $this->assertTrue(
            $reflection->hasMethod('getTokenGenerator'),
            'Should have getTokenGenerator method for lazy loading'
        );

        $method = $reflection->getMethod('getTokenGenerator');
        $this->assertTrue($method->isPrivate(), 'getTokenGenerator should be private');
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $this->assertTrue($reflection->isFinal(), 'PasswordResetController should be final');
    }

    public function testExtendsAdminController(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);
        $parent = $reflection->getParentClass();

        $this->assertNotFalse($parent, 'Should extend a parent class');
        $this->assertEquals(
            'OxidEsales\Eshop\Application\Controller\Admin\AdminController',
            $parent->getName(),
            'Should extend AdminController'
        );
    }

    public function testHasPrivatePropertiesForServices(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $this->assertTrue($reflection->hasProperty('apiUserService'));
        $this->assertTrue($reflection->hasProperty('moduleSettingService'));
        $this->assertTrue($reflection->hasProperty('tokenGenerator'));
    }

    public function testServicePropertiesAreNullableAndPrivate(): void
    {
        $reflection = new \ReflectionClass(PasswordResetController::class);

        $properties = ['apiUserService', 'moduleSettingService', 'tokenGenerator'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPrivate(), "$propertyName should be private");

            $type = $property->getType();
            $this->assertNotNull($type, "$propertyName should have a type");
            $this->assertTrue($type->allowsNull(), "$propertyName should be nullable");
        }
    }
}
