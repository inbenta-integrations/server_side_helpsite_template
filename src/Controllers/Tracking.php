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
 * Handles tracking-related endpoints.
 */
class Tracking extends Base
{
    /**
     * Logs a tracking event for the given content.
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
    public function track(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();

        if (!isset($body['type'])) {
            throw new \Exception('"type" parameter must be specified.');
        }
        $trackingType = filter_var($body['type'], FILTER_SANITIZE_SPECIAL_CHARS);
        $trackingValue = filter_var($body['value'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $trackingCode = filter_var($body['code'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $trackingComment = filter_var($body['comment'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        $this->kmApiClient->track([
            'code' => $trackingCode,
            'type' => $trackingType,
            'value' => $trackingValue,
            'comment' => $trackingComment,
        ]);

        return $response;
    }
}
