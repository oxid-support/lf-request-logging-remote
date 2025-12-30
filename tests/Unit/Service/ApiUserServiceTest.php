<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Service\ApiUserService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiUserService::class)]
final class ApiUserServiceTest extends TestCase
{
    public function testLoadApiUserReturnsFalseWhenUserNotFound(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn(false);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('email', Module::API_USER_EMAIL)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($queryBuilderFactory);

        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('load');

        $this->assertFalse($service->loadApiUser($user));
    }

    public function testLoadApiUserReturnsTrueWhenUserFound(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('user-id-123');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('email', Module::API_USER_EMAIL)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($queryBuilderFactory);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('load')
            ->with('user-id-123')
            ->willReturn(true);

        $this->assertTrue($service->loadApiUser($user));
    }

    public function testLoadApiUserReturnsFalseWhenUserLoadFails(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('user-id-123');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($queryBuilderFactory);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('load')
            ->with('user-id-123')
            ->willReturn(false);

        $this->assertFalse($service->loadApiUser($user));
    }

    public function testResetPasswordUpdatesUserPassword(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('update')->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))->method('set')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->exactly(3))->method('setParameter')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute');

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($queryBuilderFactory);
        $service->resetPassword('user-id-123');

        // If we get here without exception, the test passes
        $this->assertTrue(true);
    }
}
