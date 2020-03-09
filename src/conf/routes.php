<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

/**
 * In this file you can register all your app's endpoints.
 */

use Inbenta\ServerSideHelpsite\Controllers\Autocomplete;
use Inbenta\ServerSideHelpsite\Controllers\Categories;
use Inbenta\ServerSideHelpsite\Controllers\Contents;
use Inbenta\ServerSideHelpsite\Controllers\Errors;
use Inbenta\ServerSideHelpsite\Controllers\Home;
use Inbenta\ServerSideHelpsite\Controllers\Results;
use Inbenta\ServerSideHelpsite\Controllers\Robots;
use Inbenta\ServerSideHelpsite\Controllers\Sitemap;
use Inbenta\ServerSideHelpsite\Controllers\Tracking;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

// 404 errors handler.
$container['notFoundHandler'] = function (Container $container) {
    return function (Request $request, Response $response) use ($container): Response {
        $controller = new Errors($container);
        return $controller->getNotFoundPage($request, $response, [])->withStatus(404);
    };
};

// 405 errors handler.
$container['notAllowedHandler'] = function (Container $container) {
    return function (Request $request, Response $response) use ($container): Response {
        $controller = new Errors($container);
        return $controller->getNotFoundPage($request, $response, [])->withStatus(500);
    };
};

// Server errors handler.
$container['errorHandler'] = function (Container $container) {
    return function (Request $request, Response $response) use ($container): Response {
        $controller = new Errors($container);
        return $controller->getErrorPage($request, $response, [])->withStatus(500);
    };
};

// PHP errors handler.
$container['phpErrorHandler'] = function (Container $container) {
    return function (Request $request, Response $response) use ($container): Response {
        $controller = new Errors($container);
        return $controller->getErrorPage($request, $response, [])->withStatus(500);
    };
};

// Homepage.
$app->get('/', Home::class . ':getHomePage');

// Categories-related endpoints.
$app->group('/categories', function () {
    // Cagegories page.
    $this->get('', Categories::class . ':getCategoryPage');

    // Category page.
    $this->get('/[{categories:.*}]', Categories::class . ':getCategoryPage');
});

// `sitemap.xml` page.
$app->get('/sitemap.xml', Sitemap::class . ':getSitemapPage');

// `robots.txt` page.
$app->get('/robots.txt', Robots::class . ':getRobotsPage');

// Search results page.
$app->get('/results', Results::class . ':getResultsPage');

// Content page.
$app->get('/contents/{content:.*}', Contents::class . ':getContentPage');

// Autocomplete feature.
$app->get('/autocomplete', Autocomplete::class . ':autocomplete');

// Tracking feature.
$app->post('/tracking', Tracking::class . ':track');
