<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Framework;

use OxidEsales\GraphQL\Base\Framework\PermissionProviderInterface;
use OxidSupport\RequestLoggerRemote\Framework\PermissionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PermissionProvider::class)]
final class PermissionProviderTest extends TestCase
{
    public function testImplementsPermissionProviderInterface(): void
    {
        $provider = new PermissionProvider();

        $this->assertInstanceOf(PermissionProviderInterface::class, $provider);
    }

    public function testGetPermissionsReturnsArray(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertIsArray($permissions);
    }

    public function testGetPermissionsContainsApiUserGroup(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertArrayHasKey('oxsrequestlogger_api', $permissions);
    }

    public function testGetPermissionsContainsAdminGroup(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertArrayHasKey('oxidadmin', $permissions);
    }

    public function testApiUserGroupHasViewPermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_VIEW', $permissions['oxsrequestlogger_api']);
    }

    public function testApiUserGroupHasChangePermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_CHANGE', $permissions['oxsrequestlogger_api']);
    }

    public function testApiUserGroupHasActivatePermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_ACTIVATE', $permissions['oxsrequestlogger_api']);
    }

    public function testApiUserGroupHasPasswordResetPermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('OXSREQUESTLOGGER_PASSWORD_RESET', $permissions['oxsrequestlogger_api']);
    }

    public function testAdminGroupHasViewPermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_VIEW', $permissions['oxidadmin']);
    }

    public function testAdminGroupHasChangePermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_CHANGE', $permissions['oxidadmin']);
    }

    public function testAdminGroupHasActivatePermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('REQUEST_LOGGER_ACTIVATE', $permissions['oxidadmin']);
    }

    public function testAdminGroupHasPasswordResetPermission(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertContains('OXSREQUESTLOGGER_PASSWORD_RESET', $permissions['oxidadmin']);
    }

    public function testApiUserGroupHasExactlyFourPermissions(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertCount(4, $permissions['oxsrequestlogger_api']);
    }

    public function testAdminGroupHasExactlyFourPermissions(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertCount(4, $permissions['oxidadmin']);
    }

    public function testBothGroupsHaveSamePermissions(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $apiPermissions = $permissions['oxsrequestlogger_api'];
        $adminPermissions = $permissions['oxidadmin'];

        sort($apiPermissions);
        sort($adminPermissions);

        $this->assertSame($apiPermissions, $adminPermissions);
    }

    public function testGetPermissionsReturnsOnlyTwoGroups(): void
    {
        $provider = new PermissionProvider();
        $permissions = $provider->getPermissions();

        $this->assertCount(2, $permissions);
    }
}
