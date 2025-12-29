<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Service;

use OxidEsales\GraphQL\Base\Framework\NamespaceMapperInterface;

class NamespaceMapper implements NamespaceMapperInterface
{
    public function getControllerNamespaceMapping(): array
    {
        return [
            // Only map the base Controller namespace (not Admin subdirectory)
            // GraphQL will scan the directory for classes with #[Query] or #[Mutation] attributes
            'OxidSupport\\RequestLoggerRemote\\Controller' => __DIR__ . '/../Controller/',
        ];
    }

    public function getTypeNamespaceMapping(): array
    {
        // We reuse DataTypes from graphql-configuration-access module
        return [];
    }
}
