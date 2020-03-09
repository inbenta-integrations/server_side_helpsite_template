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
 * Handles robots-related endpoints.
 */
class Robots extends Base
{
    /** @var bool Whether to allow robots to index this helpsite. */
    private $allowIndexing;

    /**
     * Class constructor.
     *
     * @param \Slim\Container $container App's container.
     *
     * @return void
     */
    public function __construct(\Slim\Container $container)
    {
        parent::__construct($container);
        $this->allowIndexing = $container['allowIndexing'];
    }

    /**
     * Builds the `robots.txt` file.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getRobotsPage(Request $request, Response $response, array $args): Response
    {
        $robots = $this->view->generateRobotsView([
            'robotRule' => ($this->allowIndexing === true) ? 'Allow' : 'Disallow',
            'sitemapUrl' => $this->buildFullUrl('home') . '/sitemap.xml',
        ]);
        return $response->withHeader('Content-Type', 'text/plain')->write($robots);
    }
}
