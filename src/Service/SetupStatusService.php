<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * Service to check the setup status of the module.
 * Checks actual database state rather than assuming based on module activation.
 */
final class SetupStatusService implements SetupStatusServiceInterface
{
    private const MIGRATION_TABLE = 'oxmigrations_oxsrequestloggerremote';
    private const EXPECTED_MIGRATION = 'OxidSupport\\RequestLoggerRemote\\Migrations\\Version20251223000001';

    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
    }

    /**
     * Check if the module migrations have been executed.
     */
    public function isMigrationExecuted(): bool
    {
        try {
            $queryBuilder = $this->queryBuilderFactory->create();
            $result = $queryBuilder
                ->select('COUNT(*)')
                ->from(self::MIGRATION_TABLE)
                ->where('version = :version')
                ->setParameter('version', self::EXPECTED_MIGRATION)
                ->execute();

            return (int) $result->fetchOne() > 0;
        } catch (\Exception) {
            // Table doesn't exist or other error - migration not executed
            return false;
        }
    }
}
