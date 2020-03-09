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
 * Handles users sessions for Inbenta KM API.
 */
class Session
{
    /** @var string Specifies in which mode to retrieve data from knowledge base (edit / live). */
    private $env;

    /** @var int Specifies in which user type to retrieve data from knowledge base. */
    private $userType;

    /** @var string Specifies in which source to log all user events. */
    private $source;

    /** @var \Inbenta\ServerSideHelpsite\Services\Logger Logging service. */
    private $logger;

    /** @var \Symfony\Component\HttpFoundation\Session\Session Session client. */
    private $sessionClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\KmApiClient Inbenta KM API client. */
    private $kmApiClient;

    /**
     * Checks if tracking session is defined.
     *
     * @return bool True if tracking session is defined, false otherwise.
     */
    private function sessionExists()
    {
        if ($this->sessionClient->has('sessionToken')) {
            return true;
        }
        return false;
    }

    /**
     * Class constructor.
     *
     * @param \Slim\Container $container App's container.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->env = $container['mode'];
        $this->logger = $container['logger'];
        $this->source = $container['source'];
        $this->userType = $container['userType'];
        $this->sessionClient = $container['session'];
        $this->kmApiClient = $container['kmApiClient'];
    }

    /**
     * Checks if access token already exists and is valid, otherwise performs authentication
     * to Inbenta Auth API, and stores credentials in session.
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
        // Session creation endpoint needs those headers to be defined first.
        $this->kmApiClient->setCredentials([
            'env' => $this->env,
            'source' => $this->source,
            'userType' => $this->userType,
        ]);

        if (!$this->sessionExists()) {
            $this->logger->log('Creating a new session...');
            $kmResponse = $this->kmApiClient->createSession();
            $this->sessionClient->set('sessionToken', $kmResponse['sessionToken']);
            $this->kmApiClient->setCredentials([
                'sessionToken' => $this->sessionClient->get('sessionToken'),
            ]);
            $this->kmApiClient->trackUserInfo([
                'data' => [
                    'inbenta' => 'generate_session',
                ],
            ]);
        } else {
            $this->logger->log('Using existing session...');
            $this->kmApiClient->setCredentials([
                'sessionToken' => $this->sessionClient->get('sessionToken'),
            ]);
        }

        return $next($request, $response);
    }
}
