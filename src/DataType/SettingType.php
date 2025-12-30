<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\DataType;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
final class SettingType
{
    public function __construct(
        private string $name,
        private string $type,
        private bool $supported = true
    ) {
    }

    #[Field]
    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function getType(): string
    {
        return $this->type;
    }

    #[Field]
    public function isSupported(): bool
    {
        return $this->supported;
    }
}
