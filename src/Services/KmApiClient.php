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
 * Handles Knowledge Management (KM) API endpoints calls.
 * https://developers.inbenta.io/knowledge-management/api/api-routes
 */
class KmApiClient
{
    /** @var \GuzzleHttp\Client Guzzle HTTP client, used to perform calls to Inbenta APIs. */
    private $guzzleClient;

    /** @var array API credentials retrieved from Auth API. */
    private $credentials;

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->credentials = [];
        $this->guzzleClient = new \GuzzleHttp\Client([
            'handler' => \GuzzleHttp\HandlerStack::create(),
            'request.options' => [
                'timeout' => 3,
                'connect_timeout' => 3,
            ],
        ]);
    }

    /**
     * Specifies credentials to use when calling Inbenta KM API.
     *
     * @param array $credentials Credentials to use in KM API calls.
     *
     * @return void
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = array_merge($this->credentials, $credentials);
    }

    /**
     * Decodes the given API response into a JSON-flavored format.
     *
     * @param string $apiResponse Inbenta API's raw response.
     *
     * @return array The JSON-formatted response.
     */
    private function decodeResponse(string $apiResponse): array
    {
        return json_decode($apiResponse, true);
    }

    /**
     * Performs an API call to inbenta KM API with the given parameters.
     *
     * @param string $endpoint API endpoint to call.
     *
     * @param string $method API method to use.
     *
     * @param array [$headers = []] Headers to add to the request.
     *
     * @param array [$body = []] Body to pass to the request.
     *
     * @return string API's raw response.
     */
    private function request(
        string $endpoint,
        string $method,
        array $headers = [],
        array $body = []
    ): string {
        $url = $this->credentials['kmApiBaseUrl'] . $endpoint;
        $newHeaders = array_merge([
            'Content-Type' => 'application/json',
            'x-inbenta-env' => $this->credentials['env'],
            'x-inbenta-key' => $this->credentials['inbentaKey'],
            'x-inbenta-source' => $this->credentials['source'],
            'x-inbenta-user-type' => $this->credentials['userType'],
            'Authorization' => 'Bearer ' . $this->credentials['accessToken'],
        ], $headers);

        if (isset($this->credentials['sessionToken'])) {
            $newHeaders['x-inbenta-session'] = $this->credentials['sessionToken'];
        }

        $options = ['headers' => $newHeaders];
        if ($method !== 'GET') {
            $options['json'] = $body;
        }

        return $this->guzzleClient->request($method, $url, $options)->getBody();
    }

    /**
     * Get autocomplete results.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function autocomplete(array $parameters = []): array
    {
        $endpoint = '/search';
        return $this->decodeResponse($this->request($endpoint, 'POST', [], [
            'type' => 'instant',
            'splitQuery' => true,
            'tracking' => false,
            'query' => $parameters['query'],
        ]))['results'];
    }

    /**
     * Get popular contents.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getPopular(array $parameters = []): array
    {
        $endpoint = '/contents/popular' . '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get push contents.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getPush(array $parameters = []): array
    {
        $endpoint = '/contents/push' . '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get all contents.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getContents(array $parameters = []): array
    {
        $endpoint = '/contents' . '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Get last updated contents.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getLastUpdatedContents(array $parameters = []): array
    {
        // Add sortBy argument.
        $parameters['sortBy'] = 'modificationDate';
        $endpoint = '/contents' . '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get last created contents.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getLastCreatedContents(array $parameters = []): array
    {
        // Add sortBy argument.
        $parameters['sortBy'] = 'creationDate';
        $endpoint = '/contents' . '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Perform a search query.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function search(array $parameters = []): array
    {
        $endpoint = '/search';
        return $this->decodeResponse($this->request($endpoint, 'POST', [], [
            'query' => $parameters['query'],
        ]));
    }

    /**
     * Get content by slug.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getContentBySlug(array $parameters = []): array
    {
        $endpoint = '/contents/slug/' . $parameters['slug'];
        if (isset($parameters['tracking'])) {
            $endpoint .= '?tracking=' . $parameters['tracking'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get content by id.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getContentById(array $parameters = []): array
    {
        $endpoint = '/contents/' . $parameters['contentId'];
        if (isset($parameters['tracking'])) {
            $endpoint .= '?tracking=' . $parameters['tracking'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get all child categories, from the root category downwards, until maxDepth is reached.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getCategories(array $parameters = []): array
    {
        $endpoint = '/categories/';
        if (isset($parameters['maxDepth'])) {
            $endpoint .= '?maxDepth=' . $parameters['maxDepth'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get the category with the provided slug.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getCategoryBySlug(array $parameters = []): array
    {
        $endpoint = '/categories/slug/' . $parameters['slug'];
        if (isset($parameters['maxDepth'])) {
            $endpoint .= '?maxDepth=' . $parameters['maxDepth'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get child categories, from a category identified with a categoryId parameter downwards,
     * until maxDepth is reached.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getCategoryById(array $parameters = []): array
    {
        $endpoint = '/categories/' . $parameters['categoryId'];
        if (isset($parameters['maxDepth'])) {
            $endpoint .= '?maxDepth=' . $parameters['maxDepth'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get contents of the specified category ordered like in the KM App in Knowledge > categories.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getCategoryContents(array $parameters = []): array
    {
        $endpoint = '/categories/' . $parameters['categoryId'] . '/contents';
        unset($parameters['categoryId']);
        $endpoint .= '?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get contents related to the content with the specified id.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getRelatedContents(array $parameters = []): array
    {
        $endpoint = '/contents/' . $parameters['id'] . '/related';
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Send tracking data.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return void.
     */
    public function track(array $parameters = []): void
    {
        $endpoint = '/tracking/events/';
        $body = [];

        if (isset($parameters['code']) && isset($parameters['type'])) {
            $body = [
                'data' => [
                    'code' => $parameters['code'],
                    'value' => $parameters['value'] ?? null,
                    'comment' => $parameters['comment'] ?? null,
                ],
                'type' => $parameters['type'],
            ];
        } else if (isset($parameters['query']) && isset($parameters['type'])) {
            $body = [
                'data' => [
                    'query' => $parameters['query'],
                    'contentId' => $parameters['contentId'],
                ],
                'type' => $parameters['type'],
            ];
        }

        $this->request($endpoint, 'POST', [], $body);
    }

    /**
     * Get instance's app data.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getAppData(array $parameters = []): array
    {
        $endpoint = '/app/data/' . $parameters['dataId'];
        if (isset($parameters['name'])) {
            $endpoint .= '?name=' . $parameters['name'];
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Initialize a new session for events tracking.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function createSession(array $parameters = []): array
    {
        $endpoint = '/tracking/session';
        return $this->decodeResponse($this->request($endpoint, 'POST'));
    }

    /**
     * Sends session tracking data.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function trackUserInfo(array $parameters = []): array
    {
        $endpoint = '/tracking/session/user';
        $this->request($endpoint, 'POST', [], $parameters);
        return [];
    }

    /**
     * Get user questions.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getUserQuestions(array $parameters = []): array
    {
        $endpoint = '/user-questions?' . http_build_query($parameters);
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Get user question by ID.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getUserQuestionById(array $parameters = []): array
    {
        $endpoint = '/user-questions/' . $parameters['id'];
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Get user question by slug.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getUserQuestionBySlug(array $parameters = []): array
    {
        $endpoint = '/user-questions/slug/' . $parameters['slug'];
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Get all contents that matched the specified user question.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getUserQuestionContents(array $parameters = []): array
    {
        $endpoint = '/user-questions/' . $parameters['id'] . '/contents';
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }

    /**
     * Get all users questions that matched the specified content.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getContentUserQuestions(array $parameters = []): array
    {
        $endpoint = '/contents/' . $parameters['contentId'] . '/user-questions';
        if (isset($parameters['tracking'])) {
            unset($parameters['contentId']);
            $endpoint .= '?' . http_build_query($parameters);
        }
        return $this->decodeResponse($this->request($endpoint, 'GET'))['results'];
    }

    /**
     * Get the given content's decision tree.
     *
     * @param array [$parameters = []] Request parameters.
     *
     * @return array The JSON response.
     */
    public function getContentDecisionTree(array $parameters = []): array
    {
        $endpoint = '/contents/' . $parameters['contentId'] . '/decision-tree';
        return $this->decodeResponse($this->request($endpoint, 'GET'));
    }
}
