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
 * Handles contents-related endpoints.
 */
class Contents extends Base
{
    /**
     * Builds HTML page for a specific content.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getContentPage(Request $request, Response $response, array $args): Response
    {
        $contentSlug = filter_var($args['content'], FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            $contents = $this->getAllContentsInfo($this->kmApiClient->getContentBySlug([
                'slug' => $contentSlug,
                // Disabling automatic tracking to prevent duplicates with the code below...
                'tracking' => 0,
            ]), 'c');
        } catch (\Exception $exception) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        // Tracking clicks on contents, depending on the origin...
        $queryParams = $request->getQueryParams();
        // No tracking key in query string means that user performed a direct access to content.
        if (!isset($queryParams['t'])) {
            $this->kmApiClient->track([
                'type' => 'click',
                'code' => $contents[0]['tracking']['clickCode'],
            ]);
        } else if ($this->sessionClient->get('tracking') !== null) {
            $trackingKey = filter_var($queryParams['t'], FILTER_SANITIZE_SPECIAL_CHARS);
            $trackingCodes = $this->sessionClient->get('tracking');
            if (isset($trackingCodes[$trackingKey][$contentSlug])) {
                // Content is an autocomplete result...
                if (isset($trackingCodes[$trackingKey][$contentSlug]['query'])) {
                    $this->kmApiClient->track([
                        'type' => 'autocompleter_click',
                        'query' => $trackingCodes[$trackingKey][$contentSlug]['query'],
                        'contentId' => $trackingCodes[$trackingKey][$contentSlug]['contentId'],
                    ]);
                } else {
                    $this->kmApiClient->track([
                        'type' => 'click',
                        'code' => $trackingCodes[$trackingKey][$contentSlug]['clickCode'],
                    ]);
                }
            }
        }

        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView([
            'action' => $this->buildFullUrl('results'),
        ]);

        // Generating categories breadcrumb...
        $breadcrumb = array_merge([
            $this->languageManager->translate('CATEGORIES_BREADCRUMB_HOME') => $this->buildFullUrl('home'),
        ], $this->generateCategoriesBreadcrumbs($contents[0]['categories'][0]), [
            $contents[0]['title'] => $this->buildFullUrl('contents', $contents[0]['slug']),
        ]);
        $breadcrumbView = $this->view->generateCategoriesBreadcrumbView([
            'breadcrumb' => $breadcrumb,
        ]);

        // Generating content section...
        $contentView = $this->view->generateContentView([
            'results' => $contents,
            'backUrl' => array_values($breadcrumb)[count($breadcrumb) - 2],
        ]);

        // Generating content banner...
        $contentBannerView = $this->view->generateContentBannerView([]);

        // Generating popular section...
        $popular = $this->getAllContentsInfo($this->kmApiClient->getPopular(['length' => 3]), 'p');
        $popularView = $this->view->generatePopularView(['popularResults' => $popular]);

        // Adding specific metadata to HTML base layout...
        $rawMetaDescription = $contents[0]['attributes']['ANSWER_TEXT'];
        return $this->buildPage($response, $searchBoxView . $breadcrumbView . $contentView . $contentBannerView . $popularView, [
            'title' => $contents[0]['title'],
            'description' => substr(preg_replace('/\s+/', ' ', $rawMetaDescription), 0, 155),
        ]);
    }
}
