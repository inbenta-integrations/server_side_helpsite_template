<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

/**
 * In this file you can register all global middlewares for the app.
 */

$container = $app->getContainer();

// Enabling labels and metadata management for all routes...
$app->add(new \Inbenta\ServerSideHelpsite\Middlewares\Language($container));

// Enabling sessions management for all routes...
$app->add(new \Inbenta\ServerSideHelpsite\Middlewares\Session($container));

// Enabling authentication management for all routes...
$app->add(new \Inbenta\ServerSideHelpsite\Middlewares\Authentication($container));

// Enabling trailing slash for all routes...
$app->add(new \Inbenta\ServerSideHelpsite\Middlewares\TrailingSlash());

if ($container['mode'] === 'development') {
    // Enabling performance monitoring for all routes in development mode...
    $app->add(new \Inbenta\ServerSideHelpsite\Middlewares\Monitoring($container));
}
