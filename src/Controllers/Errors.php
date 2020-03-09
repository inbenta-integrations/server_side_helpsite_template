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
 * Handles requests errors.
 */
class Errors extends Base
{
    /**
     * Builds HTML page 404 errors.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getNotFoundPage(Request $request, Response $response, array $args): Response
    {
        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView(['action' => $this->buildFullUrl('results')]);

        // Generating 404 error section...
        $notFoundErrorView = $this->view->generateNotFoundErrorView([]);

        // Generating content banner...
        $contentBannerView = $this->view->generateContentBannerView([]);

        return $this->buildPage($response, $searchBoxView . $notFoundErrorView . $contentBannerView);
    }

    /**
     * Builds HTML page 500 errors.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getErrorPage(Request $request, Response $response, array $args): Response
    {
        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView(['action' => $this->buildFullUrl('results')]);

        // Generating 404 error section...
        $errorView = $this->view->generateErrorView([]);

        // Generating content banner...
        $contentBannerView = $this->view->generateContentBannerView([]);

        return $this->buildPage($response, $searchBoxView . $errorView . $contentBannerView);
    }
}
