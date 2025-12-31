<?php

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Security;

use OxidSupport\RequestLoggerRemote\Controller\ActivationController;
use OxidSupport\RequestLoggerRemote\Controller\PasswordController;
use OxidSupport\RequestLoggerRemote\Controller\SettingController;
use OxidSupport\RequestLoggerRemote\Framework\PermissionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Security tests for Authorization
 * Verifies all endpoints have proper authentication and authorization attributes
 */
#[CoversClass(ActivationController::class)]
#[CoversClass(SettingController::class)]
#[CoversClass(PasswordController::class)]
#[CoversClass(PermissionProvider::class)]
class AuthorizationSecurityTest extends TestCase
{
    // ===========================================
    // ACTIVATION CONTROLLER AUTHORIZATION
    // ===========================================

    #[DataProvider('activationControllerMethodsProvider')]
    public function testActivationControllerMethodsRequireAuth(string $method): void
    {
        $reflection = new ReflectionMethod(ActivationController::class, $method);
        $attributes = $this->getAttributeNames($reflection);

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Logged',
            $attributes,
            "$method must have #[Logged] attribute"
        );

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Right',
            $attributes,
            "$method must have #[Right] attribute"
        );
    }

    public static function activationControllerMethodsProvider(): array
    {
        return [
            ['requestLoggerIsActive'],
            ['requestLoggerActivate'],
            ['requestLoggerDeactivate'],
        ];
    }

    // ===========================================
    // SETTING CONTROLLER AUTHORIZATION
    // ===========================================

    #[DataProvider('settingControllerMethodsProvider')]
    public function testSettingControllerMethodsRequireAuth(string $method): void
    {
        $reflection = new ReflectionMethod(SettingController::class, $method);
        $attributes = $this->getAttributeNames($reflection);

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Logged',
            $attributes,
            "$method must have #[Logged] attribute"
        );

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Right',
            $attributes,
            "$method must have #[Right] attribute"
        );
    }

    public static function settingControllerMethodsProvider(): array
    {
        return [
            ['requestLoggerSettings'],
            ['requestLoggerLogLevel'],
            ['requestLoggerLogFrontend'],
            ['requestLoggerLogAdmin'],
            ['requestLoggerRedact'],
            ['requestLoggerRedactAllValues'],
            ['requestLoggerLogLevelChange'],
            ['requestLoggerLogFrontendChange'],
            ['requestLoggerLogAdminChange'],
            ['requestLoggerRedactChange'],
            ['requestLoggerRedactAllValuesChange'],
        ];
    }

    // ===========================================
    // PASSWORD CONTROLLER AUTHORIZATION
    // ===========================================

    public function testSetPasswordUsesTokenAuthNotSessionAuth(): void
    {
        $reflection = new ReflectionMethod(PasswordController::class, 'requestLoggerSetPassword');
        $attributes = $this->getAttributeNames($reflection);

        // Should NOT have #[Logged] - uses token-based auth instead
        $this->assertNotContains(
            'TheCodingMachine\GraphQLite\Annotations\Logged',
            $attributes,
            "requestLoggerSetPassword must NOT have #[Logged] - uses token auth"
        );

        // Must have #[Mutation] to be exposed via GraphQL
        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Mutation',
            $attributes,
            "requestLoggerSetPassword must have #[Mutation] attribute"
        );
    }

    public function testResetPasswordRequiresAdminAuth(): void
    {
        $reflection = new ReflectionMethod(PasswordController::class, 'requestLoggerResetPassword');
        $attributes = $this->getAttributeNames($reflection);

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Logged',
            $attributes,
            "requestLoggerResetPassword must have #[Logged] attribute"
        );

        $this->assertContains(
            'TheCodingMachine\GraphQLite\Annotations\Right',
            $attributes,
            "requestLoggerResetPassword must have #[Right] attribute"
        );
    }

    // ===========================================
    // PERMISSION PROVIDER TESTS
    // ===========================================

    public function testPermissionsAreDefinedForApiUserGroup(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertArrayHasKey('oxsrequestlogger_api', $permissions);
        $this->assertNotEmpty($permissions['oxsrequestlogger_api']);
    }

    public function testPermissionsAreDefinedForAdminGroup(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertArrayHasKey('oxidadmin', $permissions);
        $this->assertNotEmpty($permissions['oxidadmin']);
    }

    public function testAllRequiredPermissionsExist(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $requiredPermissions = [
            'REQUEST_LOGGER_VIEW',
            'REQUEST_LOGGER_CHANGE',
            'REQUEST_LOGGER_ACTIVATE',
            'OXSREQUESTLOGGER_PASSWORD_RESET',
        ];

        foreach (['oxsrequestlogger_api', 'oxidadmin'] as $group) {
            foreach ($requiredPermissions as $permission) {
                $this->assertContains(
                    $permission,
                    $permissions[$group],
                    "Permission $permission must be defined for group $group"
                );
            }
        }
    }

    public function testNoExcessivePermissionsGranted(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        // Verify no wildcard or overly broad permissions
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                $this->assertNotEquals('*', $perm, "Wildcard permissions are not allowed");
                $this->assertStringNotContainsString('ADMIN', $perm, "Should not grant general ADMIN permissions");
                $this->assertStringNotContainsString('SUPER', $perm, "Should not grant SUPER permissions");
            }
        }
    }

    // ===========================================
    // MUTATION VS QUERY SEGREGATION
    // ===========================================

    public function testReadOperationsAreQueries(): void
    {
        $readMethods = [
            [ActivationController::class, 'requestLoggerIsActive'],
            [SettingController::class, 'requestLoggerSettings'],
            [SettingController::class, 'requestLoggerLogLevel'],
            [SettingController::class, 'requestLoggerLogFrontend'],
            [SettingController::class, 'requestLoggerLogAdmin'],
            [SettingController::class, 'requestLoggerRedact'],
            [SettingController::class, 'requestLoggerRedactAllValues'],
        ];

        foreach ($readMethods as [$class, $method]) {
            $reflection = new ReflectionMethod($class, $method);
            $attributes = $this->getAttributeNames($reflection);

            $this->assertContains(
                'TheCodingMachine\GraphQLite\Annotations\Query',
                $attributes,
                "$class::$method should be a Query, not a Mutation"
            );
        }
    }

    public function testWriteOperationsAreMutations(): void
    {
        $writeMethods = [
            [ActivationController::class, 'requestLoggerActivate'],
            [ActivationController::class, 'requestLoggerDeactivate'],
            [SettingController::class, 'requestLoggerLogLevelChange'],
            [SettingController::class, 'requestLoggerLogFrontendChange'],
            [SettingController::class, 'requestLoggerLogAdminChange'],
            [SettingController::class, 'requestLoggerRedactChange'],
            [SettingController::class, 'requestLoggerRedactAllValuesChange'],
            [PasswordController::class, 'requestLoggerSetPassword'],
            [PasswordController::class, 'requestLoggerResetPassword'],
        ];

        foreach ($writeMethods as [$class, $method]) {
            $reflection = new ReflectionMethod($class, $method);
            $attributes = $this->getAttributeNames($reflection);

            $this->assertContains(
                'TheCodingMachine\GraphQLite\Annotations\Mutation',
                $attributes,
                "$class::$method should be a Mutation, not a Query"
            );
        }
    }

    // ===========================================
    // HELPER METHODS
    // ===========================================

    private function getAttributeNames(ReflectionMethod $reflection): array
    {
        return array_map(
            fn($attr) => $attr->getName(),
            $reflection->getAttributes()
        );
    }
}
