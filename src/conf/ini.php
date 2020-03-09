<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

/**
 * In this file you can add any PHP-related configuration.
 */

use Symfony\Component\Dotenv\Dotenv;

/**
 * When working locally or on a custom server, environment variables will be retrieved from the
 * `.env` file, instead of Heroku runtime.
 */
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__ . '/../../.env');
}

/** In preproduction and production modes, we don't want to show any error or warning to end user. */
ini_set('display_errors', getenv('ENV') === 'development');
ini_set('display_startup_errors', getenv('ENV') === 'development');
error_reporting(E_ALL);

/** Session cookie securisation. */
ini_set('session.cookie_secure', true);
ini_set('session.cookie_httponly', true);

/** Common PHP configuration security settings. */
set_time_limit(10);
ini_set('memory_limit', '10M');
ini_set('allow_url_fopen', false);
ini_set('allow_url_include', false);
ini_set('doc_root', __DIR__ . '/../../');
ini_set('open_basedir', __DIR__ . '/../../:/tmp/');

date_default_timezone_set('Europe/Paris');
