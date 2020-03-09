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
 * Handles categories-related endpoints.
 */
class Categories extends Base
{
    /**
     * Builds HTML page for a specific category.
     *
     * @param \Slim\Http\Request $request HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param array $args Route parameters.
     *
     * @return \Slim\Http\Response HTTP response.
     */
    public function getCategoryPage(Request $request, Response $response, array $args): Response
    {
        $categorySlug = filter_var($args['categories'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        // Retrieving category...
        try {
            // When no slug is specified, retrieve root category...
            if ($categorySlug === '') {
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
            } else {
                if ($this->getFromCache('category_' . $categorySlug) === false) {
                    $this->logger->log('Storing category_' . $categorySlug . ' in cache...');
                    $category = $this->kmApiClient->getCategoryBySlug([
                        'slug' => $categorySlug,
                        'maxDepth' => 1,
                    ]);
                    $this->storeInCache('category_' . $categorySlug, $category);
                } else {
                    $this->logger->log('Using cached category_' . $categorySlug . '...');
                }
                $categories = $this->getFromCache('category_' . $categorySlug);
            }
        } catch (\Exception $exception) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        $categories = $this->getAllCategoriesInfo($categories);
        $categoryId = intval($categories[0]['id']);
        $subCategories = $this->getSubcategories($categories, $categoryId);
        $categoryContents = $this->kmApiClient->getCategoryContents(['categoryId' => $categoryId]);
        $categoryContents = $this->getAllContentsInfo($categoryContents, 'cc');

        // Generating search box...
        $searchBoxView = $this->view->generateSearchBoxView([
            'action' => $this->buildFullUrl('results'),
        ]);

        // Generating categories breadcrumb...
        $breadcrumb = array_merge([
            $this->languageManager->translate('CATEGORIES_BREADCRUMB_HOME') => $this->buildFullUrl('home'),
        ], $this->generateCategoriesBreadcrumbs($categoryId));
        $breadcrumbView = $this->view->generateCategoriesBreadcrumbView([
            'breadcrumb' => $breadcrumb,
        ]);

        // Generating categories section...
        $categoriesView = $this->view->generateCategoriesView([
            'subCategories' => $subCategories,
            'backUrl' => array_values($breadcrumb)[count($breadcrumb) - 2],
            'categoryContents' => $categoryContents,
            'baseCategory' => $this->getCategory($categories, $categoryId),
        ]);

        // Generating category banner...
        $categoryBannerView = $this->view->generateCategoryBannerView([]);

        // Generating popular section...
        $popular = $this->getAllContentsInfo($this->kmApiClient->getPopular(['length' => 3]), 'p');
        $popularView = $this->view->generatePopularView(['popularResults' => $popular]);

        // Adding specific metadata to HTML base layout...
        $categoryMetadata = $this->languageManager->getCategoryMetadata($categories[0]['name']);
        return $this->buildPage($response, $searchBoxView . $breadcrumbView . $categoriesView . $categoryBannerView . $popularView, [
            'title' => $categoryMetadata['title'],
            'description' => $categoryMetadata['description'],
        ]);
    }
}
