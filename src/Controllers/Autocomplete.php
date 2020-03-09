<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handles autocomplete-related endpoints.
 */
class Autocomplete extends Base
{
    /**
     * Performs an autocomplete request to Inbenta API.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     *
     * @throws \Exception If `type` parameter does not exist.
     */
    public function autocomplete(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['query'])) {
            throw new \Exception('"query" parameter must be specified.');
        }
        $query = filter_var($queryParams['query'], FILTER_SANITIZE_SPECIAL_CHARS);

        $results = $this->kmApiClient->autocomplete([
            'query' => $query,
        ]);

        // We don't use `getAllContentsInfo` here as we only need a few info and search query
        // is not tracked as usual.
        // Last tracking codes are stored in session to handle events tracking on next page call.
        $currentTrackingInfo = $this->sessionClient->get('tracking') ?? [];
        $additionalTrackingInfo = ['a' => []];
        $contents = [];
        foreach ($results as $result) {
            $content = [
                'id' => $result['id'],
                'titleHighlight' => $result['titleHighlight'],
                'seoFriendlyUrl' => $this->buildFullUrl('contents', $result['slug']) . '?t=a',
            ];
            $contents[] = $content;
            // Storing content's tracking info...
            $additionalTrackingInfo['a'][$result['slug']] = [
                'query' => $query,
                'contentId' => $result['id'],
            ];
        }
        $finalTrackingInfo = array_merge($currentTrackingInfo, $additionalTrackingInfo);
        $this->sessionClient->set('tracking', $finalTrackingInfo);

        return $response->withJson($contents);
    }
}
