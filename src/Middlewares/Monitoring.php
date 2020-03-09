<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Middlewares;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handles application performance monitoring.
 */
class Monitoring
{
    /** @var \Inbenta\ServerSideHelpsite\Services\Logger Logging service. */
    private $logger;

    /**
     * Class constructor.
     *
     * @param \Slim\Container $container App's container.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->logger = $container['logger'];
    }

    /**
     * Logs application performance.
     *
     * @param \Slim\Http\Request $request  HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param \callable $next Next middleware to call.
     *
     * @return \Slim\Http\Response Updated HTTP response for the next handler.
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $startTime = microtime(true);
        $newResponse = $next($request, $response);
        $memoryUsage = (memory_get_peak_usage() / (1024 * 1000));
        $this->logger->log('Elapsed time: ' . (microtime(true) - $startTime) . ' seconds.');
        $this->logger->log('Average processor consumption: ' . (sys_getloadavg()[0] * 100) . '%.');
        $this->logger->log('Maximum memory consumption: ' . $memoryUsage . 'MB.');
        return $newResponse;
    }
}
