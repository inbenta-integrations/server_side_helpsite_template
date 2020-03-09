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
 * Handles authentication to Inbenta Auth API, and tokens caching for subsequent API calls.
 */
class Authentication
{
    /** @var array Credentials (key / secret) used for authentication to Inbenta Auth API. */
    private $credentials;

    /** @var \Inbenta\ServerSideHelpsite\Services\Logger Logging service. */
    private $logger;

    /** @var \Object Caching client. */
    private $cacheClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\KmApiClient Inbenta KM API client. */
    private $kmApiClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\AuthApiClient Inbenta Auth API client. */
    private $authApiClient;

    /**
     * Checks if current access token has expired.
     *
     * @return bool True if access token has expired, false otherwise.
     */
    private function tokenHasExpired()
    {
        if ($this->cacheClient->get('expiration') === false) {
            return true;
        }
        if ($this->cacheClient->get('expiration') - time() <= 0) {
            return true;
        }
        return false;
    }

    /**
     * Checks if current access token needs to be refreshed (expiration is in less than 5 min).
     *
     * @return bool True if access token needs to be refreshed, false otherwise.
     */
    public function tokenNeedsToBeRefreshed()
    {
        if ($this->cacheClient->get('expiration') === false) {
            return true;
        }
        if (($this->cacheClient->get('expiration') - time()) < (60 * 5)) {
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
        $this->logger = $container['logger'];
        $this->cacheClient = $container['cache'];
        $this->kmApiClient = $container['kmApiClient'];
        $this->credentials = $container['authentication'];
        $this->authApiClient = $container['authApiClient'];
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
        if ($this->tokenHasExpired()) {
            $this->logger->log('Generating a new access token...');
            $authResponse = $this->authApiClient->auth([
                'inbentaKey' => $this->credentials['key'],
                'secret' => $this->credentials['secret'],
            ]);
            $this->cacheClient->set('expiration', $authResponse['expiration']);
            $this->cacheClient->set('accessToken', $authResponse['accessToken']);
            $this->cacheClient->set('kmApiBaseUrl', $authResponse['apis']['knowledge']);
        } else if ($this->tokenNeedsToBeRefreshed()) {
            $this->logger->log('Refreshing existing access token...');
            $authResponse = $this->authApiClient->refreshAuthToken([
                'inbentaKey' => $this->credentials['key'],
                'accessToken' => $this->cacheClient->get('accessToken'),
            ]);
            $this->cacheClient->set('expiration', $authResponse['expiration']);
            $this->cacheClient->set('accessToken', $authResponse['accessToken']);
        }

        $this->logger->log('Using cached access token...');
        $this->kmApiClient->setCredentials([
            'inbentaKey' => $this->credentials['key'],
            'accessToken' => $this->cacheClient->get('accessToken'),
            'kmApiBaseUrl' => $this->cacheClient->get('kmApiBaseUrl') . '/v1',
        ]);
        return $next($request, $response);
    }
}
