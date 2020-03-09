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
 * Handles sitemap-related endpoints.
 */
class Sitemap extends Base
{
    /**
     * Builds the `sitemap.xml` file.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getSitemapPage(Request $request, Response $response, array $args): Response
    {
        if ($this->getFromCache('sitemap_urls') === false) {
            $this->logger->log('Storing sitemap_urls in cache...');
            $lastModificationDate = date('Y-m-d');

            // Main URLs.
            $sitemap_urls = [
                ['url' => $this->buildFullUrl('home'), 'lastModification' => $lastModificationDate],
            ];

            // Contents URLs.
            $page = 0;
            $contents = [];
            $hasMore = true;
            $pageLength = 100; // TODO : Get from environment variable
            while ($hasMore === true) {
                $page += 1;
                $additionalContents = $this->kmApiClient->getContents([
                    'length' => $pageLength,
                    'offset' => ($page - 1) * $pageLength,
                ]);
                $contents = array_merge($contents, $additionalContents['results']);
                $hasMore = $additionalContents['hasMore'];
            }

            // Adding each content slug to the entries list...
            foreach ($contents as $content) {
                $sitemap_urls[] = [
                    'url' => $this->buildFullUrl('contents', $content['slug']),
                    'lastModification' => $lastModificationDate,
                ];
            }

            // Categories URLs.
            $categories = $this->kmApiClient->getCategories(['maxDepth' => -1]);
            foreach ($categories as $category) {
                $sitemap_urls[] = [
                    'url' => $this->buildFullUrl('categories', $category['slug']),
                    'lastModification' => $lastModificationDate,
                ];
            }

            // UQS PAGES AND UQS ELEMENTS
            // TODO : Generate user-question URLs
            // [ 'url' => $this->getAbsoluteUrl('userquestions'), 'lastModification' => $lastModificationDate ],
            $this->storeInCache('sitemap_urls', $sitemap_urls);
        } else {
            $this->logger->log('Using cached sitemap_urls...');
        }

        $sitemap = $this->view->generateSitemapView([
            'contents' => $this->getFromCache('sitemap_urls'),
        ]);
        return $response->withHeader('Content-Type', 'application/xml')->write($sitemap);
    }
}
