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
 * Handles home endpoint.
 */
class Home extends Base
{
    /**
     * Builds HTML page for home page.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getHomePage(Request $request, Response $response, array $args): Response
    {
        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView(['action' => $this->buildFullUrl('results')]);

        // Generating popular section...
        $popular = $this->getAllContentsInfo($this->kmApiClient->getPopular(['length' => 3]), 'p');
        $popularView = $this->view->generatePopularView(['popularResults' => $popular]);

        // Generating home banner...
        $homeBannerView = $this->view->generateHomeBannerView([]);

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

        return $this->buildPage($response, $searchBoxView . $popularView . $homeBannerView . $categoriesView);
    }
}
