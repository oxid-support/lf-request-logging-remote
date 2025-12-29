<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidSupport\RequestLoggerRemote\Core\Module;

$sMetadataVersion = '2.1';

$aModule = [
    'id' => Module::MODULE_ID,
    'title' => 'OXS :: Logging Framework :: Request Logger Remote',
    'description' => 'GraphQL API for remote configuration and activation of the Request Logger module.
Provides queries and mutations to manage request logger settings and module activation via GraphQL.',
    'version' => '1.0.0',
    'author' => 'OXID Support',
    'email' => 'support@oxid-esales.com',
    'url' => 'https://oxid-esales.com',
    'events' => [
        'onActivate' => \OxidSupport\RequestLoggerRemote\Core\ModuleEvents::class . '::onActivate',
    ],
    'settings' => [
        [
            'group' => 'oxsrequestloggerremote_main',
            'name'  => Module::SETTING_SETUP_TOKEN,
            'type'  => 'str',
            'value' => '',
        ],
    ],
];
