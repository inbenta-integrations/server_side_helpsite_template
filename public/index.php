<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite;

require_once __DIR__ . '/../vendor/autoload.php';

// Overriding default PHP configuration if needed...
require_once __DIR__ . '/../src/conf/ini.php';

$configuration = require_once __DIR__ . '/../src/conf/app.php';
$app = new \Slim\App($configuration);

// Registering services...
require_once __DIR__ . '/../src/conf/services.php';

// Registering middlewares...
require_once __DIR__ . '/../src/conf/middlewares.php';

// Registering routes...
require_once __DIR__ . '/../src/conf/routes.php';

$app->run();
