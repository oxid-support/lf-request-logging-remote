<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSupport\RequestLoggerRemote\Service\SetupStatusService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SetupStatusService::class)]
final class SetupStatusServiceTest extends TestCase
{
    private const MIGRATION_TABLE = 'oxmigrations_oxsrequestloggerremote';
    private const EXPECTED_MIGRATION = 'OxidSupport\\RequestLoggerRemote\\Migrations\\Version20251223000001';

    /**
     * Migration was executed - count returns 1
     */
    public function testIsMigrationExecutedReturnsTrueWhenMigrationExists(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('1');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())->method('select')->with('COUNT(*)')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->with(self::MIGRATION_TABLE)->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->with('version = :version')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->with('version', self::EXPECTED_MIGRATION)->willReturnSelf();
        $queryBuilder->expects($this->once())->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertTrue($result);
    }

    /**
     * Migration not executed - count returns 0
     */
    public function testIsMigrationExecutedReturnsFalseWhenMigrationDoesNotExist(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('0');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertFalse($result);
    }

    /**
     * Migration table doesn't exist - exception thrown
     */
    public function testIsMigrationExecutedReturnsFalseWhenTableDoesNotExist(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('execute')->willThrowException(new \Exception('Table does not exist'));

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertFalse($result);
    }

    /**
     * Database connection error
     */
    public function testIsMigrationExecutedReturnsFalseOnDatabaseError(): void
    {
        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')
            ->willThrowException(new \Exception('Database connection failed'));

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertFalse($result);
    }

    /**
     * Count returns integer greater than 1 (edge case, shouldn't happen but should still return true)
     */
    public function testIsMigrationExecutedReturnsTrueWhenCountGreaterThanOne(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn('5');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertTrue($result);
    }

    /**
     * fetchOne returns null
     */
    public function testIsMigrationExecutedReturnsFalseWhenFetchOneReturnsNull(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertFalse($result);
    }

    /**
     * fetchOne returns false
     */
    public function testIsMigrationExecutedReturnsFalseWhenFetchOneReturnsFalse(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchOne')
            ->willReturn(false);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($result);

        $queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactory->method('create')->willReturn($queryBuilder);

        $result = $this->getSut(queryBuilderFactory: $queryBuilderFactory)->isMigrationExecuted();

        $this->assertFalse($result);
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null,
    ): SetupStatusService {
        return new SetupStatusService(
            queryBuilderFactory: $queryBuilderFactory ?? $this->createStub(QueryBuilderFactoryInterface::class),
        );
    }
}
