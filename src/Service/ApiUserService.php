<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;

/**
 * Service for API user operations.
 */
final class ApiUserService implements ApiUserServiceInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
    }

    public function loadApiUser(User $user): bool
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('oxuser')
            ->where('OXUSERNAME = :email')
            ->setParameter('email', Module::API_USER_EMAIL);

        $userId = $queryBuilder->execute()->fetchOne();

        if (!$userId) {
            return false;
        }

        return $user->load($userId);
    }

    public function resetPassword(string $userId): void
    {
        $placeholder = bin2hex(random_bytes(32));

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('oxuser')
            ->set('OXPASSWORD', ':placeholder')
            ->set('OXPASSSALT', ':salt')
            ->where('OXID = :userId')
            ->setParameter('placeholder', $placeholder)
            ->setParameter('salt', '')
            ->setParameter('userId', $userId);

        $queryBuilder->execute();
    }
}
