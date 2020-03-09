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
 * Handles search results-related endpoints.
 */
class Results extends Base
{
    /**
     * Builds HTML page for a search query results.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     *
     * @throws \Exception If `query` parameter does not exist.
     */
    public function getResultsPage(Request $request, Response $response, array $args): Response
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['query'])) {
            throw new \Exception('"query" parameter must be specified.');
        }
        $searchQuery = filter_var($queryParams['query'], FILTER_SANITIZE_SPECIAL_CHARS);
        $results = ($this->kmApiClient->search(['query' => $searchQuery]));
        $contents = $this->getAllContentsInfo($results['results'], 'r');
        $searchRateCode = $results['tracking']['searchCode'];

        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView([
            'action' => $this->buildFullUrl('results'),
        ]);

        // Generating results section...
        $resultsView = $this->view->generateResultsView([
            'results' => $contents,
            'lastQuery' => $searchQuery,
            'searchRateCode' => $searchRateCode,
            'backUrl' => $this->buildFullUrl('home'),
        ]);

        // Generating results banner...
        $resultsBannerView = $this->view->generateResultsBannerView([]);

        // Generating categories section...
        if ($this->getFromCache('root_category') === false) {
            $this->logger->log('Storing root_category in cache...');
            $this->storeInCache('root_category', $this->kmApiClient->getCategoryById([
                'categoryId' => 0,
                'maxDepth' => 1,
            ]));
        } else {
            $this->logger->log('Using cached root_category...');
        }
        $categories = $this->getFromCache('root_category');
        $categories = $this->getAllCategoriesInfo($categories);
        $subCategories = $this->getSubcategories($categories, 0);
        $categoryContents = $this->kmApiClient->getCategoryContents(['categoryId' => 0]);
        $categoriesView = $this->view->generateCategoriesView([
            'backUrl' => $this->buildFullUrl('home'),
            'baseCategory' => $this->getCategory($categories, 0),
            'subCategories' => $subCategories,
            'categoryContents' => $categoryContents,
        ]);

        // Adding specific metadata to HTML base layout...
        return $this->buildPage($response, $searchBoxView . $resultsView . $resultsBannerView . $categoriesView, [
            'title' => $this->languageManager->translate('METADATA_CONTENTS_TITLE'),
            'description' => $this->languageManager->translate('METADATA_CONTENTS_DESCRIPTION'),
        ]);
    }
}
