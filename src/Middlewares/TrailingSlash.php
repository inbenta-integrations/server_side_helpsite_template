<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Disables strict mode on the API routes. In strict mode, routes with a trailing slash
 * are considered as different from route without (e.g. "/api/route" won't match "/api/route/").
 */
class TrailingSlash {
  /**
   * Always removes trailing slash from route.
   *
   * @param \Slim\Http\Request $request  HTTP request.
   *
   * @param \Slim\Http\Response $response HTTP response.
   *
   * @param \callable $next Next middleware to call.
   *
   * @return \Slim\Http\Response Updated HTTP response for the next handler.
   */
  public function __invoke(Request $request, Response $response, callable $next) : Response {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path !== '/' && substr($path, -1) === '/') {
      $uri = $uri->withPath(substr($path, 0, -1));
      if($request->getMethod() === 'GET') {
        return $response->withRedirect($uri->withPort(443), 301);
      } else {
        return $next($request->withUri($uri), $response);
      }
    }
    return $next($request, $response);
  }
}
