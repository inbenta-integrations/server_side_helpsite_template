<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

/**
 * In this file you can register all your app's services into the dependency container.
 */

namespace Inbenta\ServerSideHelpsite;

use Memcached;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

$container = $app->getContainer();

// Registering Slim template engine...
$container['view'] = function ($container) {
    return new Services\View($container['customTemplatesDirectory']);
};

// Registering Inbenta KM API client...
$container['kmApiClient'] = function ($container) {
    return new Services\KmApiClient();
};

// Registering language management service...
$container['languageManager'] = function ($container) {
    return new Services\LanguageManager($container['lang']);
};

// Registering Inbenta Auth API client...
$container['authApiClient'] = function ($container) {
    return new Services\AuthApiClient('https://api.inbenta.io/v1');
};

// Registering sessions service...
$container['session'] = function ($container) {
    $session = null;
    // We use Memcached in preproduction and production modes to support load-balancing.
    if ($container['mode'] === 'development') {
        $sessionStorage = new NativeSessionStorage([], new NativeFileSessionHandler('/tmp'));
        $session = new Session($sessionStorage);
    } else {
        $memCached = new MemcachedSessionHandler($container['cache']);
        $sessionStorage = new NativeSessionStorage([], $memCached);
        $session = new Session($sessionStorage);
    }
    $session->start();
    return $session;
};

// Registering caching service...
$container['cache'] = function ($container) {
    // We use Memcached in preproduction and production modes to support load-balancing.
    if ($container['mode'] === 'development') {
        return new \Inbenta\ServerSideHelpsite\Utils\FileCached('/tmp/memcached.json');
    }
    $memCached = new Memcached();
    $memCached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
    $memCached->setOption(Memcached::OPT_TCP_NODELAY, true);
    $memCached->setOption(Memcached::OPT_NO_BLOCK, false);
    $memCached->setOption(Memcached::OPT_CONNECT_TIMEOUT, 2000); // ms
    $memCached->setOption(Memcached::OPT_POLL_TIMEOUT, 2000); // ms
    $memCached->setOption(Memcached::OPT_RECV_TIMEOUT, 750 * 1000); // us
    $memCached->setOption(Memcached::OPT_SEND_TIMEOUT, 750 * 1000); // us
    $memCached->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
    $memCached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
    $memCached->setOption(Memcached::OPT_RETRY_TIMEOUT, 2);
    $memCached->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 1);
    $memCached->setOption(Memcached::OPT_AUTO_EJECT_HOSTS, true);
    $memCached->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));
    // We use a consistent connection to memcached, so only add in the servers first time through
    // otherwise we end up duplicating our connections to the server.
    if (!$memCached->getServerList()) {
        $servers = explode(',', getenv('MEMCACHIER_SERVERS'));
        foreach ($servers as $server) {
            $parts = explode(':', $server);
            $memCached->addServer($parts[0], $parts[1]);
        }
    }
    return $memCached;
};

// Registering logger service...
$container['logger'] = function ($container) {
    return new Services\Logger($container['mode'], '[INBENTA]');
};
