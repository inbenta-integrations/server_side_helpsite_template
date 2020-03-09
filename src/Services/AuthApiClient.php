<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Services;

/**
 * Handles Auth API endpoints calls.
 * https://developers.inbenta.io/general/authorization/api-routes
 */
class AuthApiClient
{
    /** @var \GuzzleHttp\Client Guzzle HTTP client, used to perform calls to Inbenta APIs. */
    private $guzzleClient;

    /** @var string Inbenta Auth API's base URL. */
    private $apiBaseUrl;

    /**
     * Class constructor.
     *
     * @param string $apiBaseUrl Inbenta Auth API's base URL.
     *
     * @return void
     */
    public function __construct(string $apiBaseUrl)
    {
        $this->guzzleClient = new \GuzzleHttp\Client([
            'handler' => \GuzzleHttp\HandlerStack::create(),
            'request.options' => [
                'timeout' => 3,
                'connect_timeout' => 3,
            ],
        ]);
        $this->apiBaseUrl = $apiBaseUrl;
    }

    /**
     * Decodes the given API response into a JSON-flavored format.
     *
     * @param string $requestResponse Curl request's raw response.
     *
     * @return array The JSON-formatted response.
     */
    private function decodeResponse($requestResponse): array
    {
        return json_decode($requestResponse, true);
    }

    /**
     * Performs an API call to inbenta KM API with the given parameters.
     *
     * @param string $method API method to use.
     *
     * @param string $endpoint API endpoint to call.
     *
     * @param array [$headers = []] Headers to add to the request.
     *
     * @param array [$body = []] Body to pass to the request.
     *
     * @return string API's raw response.
     */
    private function request(string $endpoint, string $method, array $headers = [], array $body = []): string
    {
        $url = $this->apiBaseUrl . $endpoint;
        $newHeaders = array_merge(['Content-Type' => 'application/json'], $headers);
        $options = ['headers' => $newHeaders];
        if ($method !== 'GET') {
            $options['json'] = $body;
        }

        return $this->guzzleClient->request($method, $url, $options)->getBody();
    }

    /**
     * Perform an authentication to API.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function auth(array $parameters = []): array
    {
        $endpoint = '/auth';
        $headers = [
            'x-inbenta-key' => $parameters['inbentaKey'],
        ];
        $body = [
            'secret' => $parameters['secret'],
        ];
        return $this->decodeResponse($this->request($endpoint, 'POST', $headers, $body));
    }

    /**
     * Refresh API access token.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function refreshAuthToken(array $parameters = []): array
    {
        $endpoint = '/refreshToken';
        $headers = [
            'x-inbenta-key' => $parameters['inbentaKey'],
            'Authorization' => 'Bearer ' . $parameters['accessToken'],
        ];
        return $this->decodeResponse($this->request($endpoint, 'POST', $headers));
    }
}
