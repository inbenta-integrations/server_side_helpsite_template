<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Controllers;

use Slim\Container;
use Slim\Http\Response;

/**
 * Base controller, contains all attributes and methods common to other controllers.
 */
class Base
{
    /** @var string Helpsite's base (root) URL. */
    private $baseUrl;

    /** @var array Helpsite's sections slugs. */
    private $sectionsSlugs;

    /** @var string App mode. Allows to dynamically change templates depending on the environment. */
    protected $mode;

    /** @var \Inbenta\ServerSideHelpsite\Services\View HTML views generator. */
    protected $view;

    /** @var \Inbenta\ServerSideHelpsite\Services\Logger Logging service. */
    protected $logger;

    /** @var \Object Caching client. */
    protected $cacheClient;

    /** @var array Display options used to customize HTML templates. */
    protected $displayOptions;

    /** @var \Symfony\Component\HttpFoundation\Session\Session Session client. */
    protected $sessionClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\KmApiClient Inbenta KM API client. */
    protected $kmApiClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\LanguageManager Labels translation service. */
    protected $languageManager;

    /**
     * Stores data into cache with the given key.
     *
     * @param string $key Data key.
     *
     * @param mixed $value Data to store in cache.
     *
     * @return void
     */
    protected function storeInCache(string $key, $value)
    {
        // Cache for 5 min.
        $this->cacheClient->set($key, $value, 1800);
    }

    /**
     * Retrieves data from cache by the given key, if exist.
     *
     * @param string $key Data key.
     *
     * @return mixed Cached data if exist, `false` otherwise.
     */
    protected function getFromCache(string $key)
    {
        return $this->cacheClient->get($key);
    }

    /**
     * Generates the full URL to a specific section, category or content.
     *
     * @param string $section Website section.
     *
     * @param string $section Additional slug, if applicable.
     *
     * @return string The generated full URL.
     */
    protected function buildFullUrl(string $section, $slug = ''): string
    {
        return $this->baseUrl . $this->sectionsSlugs[$section] . ($slug !== '' ? "/{$slug}" : '');
    }

    /**
     * Generates the categories breadcrumbs list.
     *
     * @param string $categoryId Starting category ID. The list will be generated from this ID.
     *
     * @return array The generated categories breadcrumbs list.
     */
    protected function generateCategoriesBreadcrumbs(string $categoryId): array
    {
        $categoriesBreadcrumbs = [];
        $currentCategoryId = $categoryId;
        while ($currentCategoryId !== 0 && $currentCategoryId !== '-') {
            $category = $this->kmApiClient->getCategoryById(['categoryId' => $currentCategoryId]);
            $category = $category[0];
            // Adding each category parent in this order to correctly display order in template...
            $categoriesBreadcrumbs = array_merge([
                $category['name'] => $this->buildFullUrl('categories', $category['slug']),
            ], $categoriesBreadcrumbs);
            $currentCategoryId = $category['parent'];
        }
        return $categoriesBreadcrumbs;
    }

    /**
     * Adds related contents and SEO-friendly URLs to KM API contents results, and stores
     * necessary tracking codes in session for events logging.
     *
     * @param array $contents KM API contents results.
     *
     * @param string $trackingKey Tracking key. Used to determine tracking code from content's URL.
     *
     * @return array KM API contents results with extra information.
     */
    protected function getAllContentsInfo(array $contents, string $trackingKey): array
    {
        // Last tracking codes are stored in session to handle events tracking on next page call.
        $currentTrackingInfo = $this->sessionClient->get('tracking') ?? [];
        // Tracking key used for related contents.
        $rTrackingKey = 'r' . $trackingKey;
        $additionalTrackingInfo = [$trackingKey => [], $rTrackingKey => []];

        $updatedContents = [];
        foreach ($contents as $content) {
            $updatedContent = $content;
            // Adding related contents...
            if ($updatedContent['hasRelated'] === true) {
                $updatedContent['related'] = $this->kmApiClient->getRelatedContents([
                    'id' => $updatedContent['id'],
                ]);
                foreach ($updatedContent['related'] as $relatedContentId => $relatedContent) {
                    $updatedRelatedContent = $relatedContent;
                    $contentUrl = $this->buildFullUrl('contents', $relatedContent['slug']);
                    $updatedRelatedContent['seoFriendlyUrl'] = $contentUrl . '?t=' . $rTrackingKey;
                    $updatedContent['related'][$relatedContentId] = $updatedRelatedContent;
                    // Storing related content's tracking codes...
                    $tracking = $relatedContent['tracking'];
                    $additionalTrackingInfo[$rTrackingKey][$relatedContent['slug']] = $tracking;
                }
            }
            $contentUrl = $this->buildFullUrl('contents', $updatedContent['slug']);
            $updatedContent['seoFriendlyUrl'] = $contentUrl . '?t=' . $trackingKey;
            $updatedContents[] = $updatedContent;
            // Storing content's tracking codes...
            $additionalTrackingInfo[$trackingKey][$content['slug']] = $content['tracking'];
        }

        $finalTrackingInfo = array_merge($currentTrackingInfo, $additionalTrackingInfo);
        $this->sessionClient->set('tracking', $finalTrackingInfo);

        return $updatedContents;
    }

    /**
     * Adds and SEO-friendly URLs to KM API categories results.
     *
     * @param array $contents KM API categories results.
     *
     * @return array KM API categories results with extra information.
     */
    protected function getAllCategoriesInfo(array $categories): array
    {
        return array_map(function ($category) {
            return array_merge($category, [
                'seoFriendlyUrl' => $this->buildFullUrl('categories', $category['slug']),
            ]);
        }, $categories);
    }

    /**
     * Returns the category with the specified ID, in the given list.
     *
     * @param array $categories List of categories to look into for the category.
     *
     * @param int $categoryId ID of the category to look for.
     *
     * @return array Category if found, empty array otherwise.
     */
    protected function getCategory(array $categories, int $categoryId): array
    {
        foreach ($categories as $category) {
            if (intval($category['id']) === $categoryId) {
                return $category;
            }
        }
        return [];
    }

    /**
     * Returns all sub-categories of the category with the specified ID, in the given list.
     *
     * @param array $categories List of categories to look into for sub-categories.
     *
     * @param int $categoryId ID of the parent category.
     *
     * @return array Category's sub-categories.
     */
    protected function getCategoriesTree(array $categories, int $categoryId): array
    {
        $categoriesTree = [];
        $root_category = $this->getCategory($categories, $categoryId);
        $root_category['subCategories'] = [];
        foreach ($root_category['children'] as $subCategoryId) {
            $root_category['subCategories'][] = $this->getCategoriesTree($categories, $subCategoryId);
        }
        return $root_category;
    }

    /**
     * Returns all sub-categories of the category with the specified ID, in the given list.
     *
     * @param array $categories List of categories to look into for sub-categories.
     *
     * @param int $categoryId ID of the parent category.
     *
     * @return array Category's sub-categories.
     */
    protected function getSubcategories(array $categories, int $categoryId): array
    {
        // `array_values` is used to reset keys after having filtered results.
        $subCategories = array_values(array_filter($categories, function ($category) use ($categoryId) {
            return $category['parent'] !== '-' && intval($category['parent']) === $categoryId;
        }));

        // Associating categories in groups of X for design...
        $categoriesPerLine = $this->displayOptions['categoriesPerLine'];
        $subCategoriesLength = count($subCategories);
        $finalSubCategories = [];
        if ($subCategoriesLength > $categoriesPerLine) {
            $numberOfGroups = ceil($subCategoriesLength / $categoriesPerLine);
            for ($i = 0; $i < $numberOfGroups; $i++) {
                $finalSubCategories[] = array_slice($subCategories, $i * $categoriesPerLine, $categoriesPerLine);
            }
        } else {
            $finalSubCategories[] = $subCategories;
        }
        return $finalSubCategories;
    }

    /**
     * Class constructor.
     *
     * @param \Slim\Container $container App's container.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->mode = $container['mode'];
        $this->view = $container['view'];
        $this->logger = $container['logger'];
        $this->baseUrl = $container['baseUrl'];
        $this->cacheClient = $container['cache'];
        $this->sessionClient = $container['session'];
        $this->kmApiClient = $container['kmApiClient'];
        $this->sectionsSlugs = $container['sectionsSlugs'];
        $this->displayOptions = $container['displayOptions'];
        $this->languageManager = $container['languageManager'];
        $this->view->setGlobalParams([
            'lang' => $this->languageManager->getLang(),
            'options' => $container['displayOptions'],
            'metaData' => $container['metaData'],
            'translate' => [$this->languageManager, 'translate'],
            'robots' => ($this->mode === 'production' ? 'index, follow' : 'noindex, nofollow'),
        ]);
    }

    /**
     * Generates a HTML page with the given contents and parameters, and populates the HTTP response.
     *
     * @param \Slim\Http\Response $response HTTP response before HTML page generation.
     *
     * @param string $contents Raw HTML content to insert into HTML page.
     *
     * @param array [$params = []] Parameters to pass to base HTML template.
     *
     * @return Response Populated HTTP response.
     */
    public function buildPage(Response $response, string $contents, array $params = []): Response
    {
        return $this->view->render($response, 'base.html', array_merge([
            'header' => $this->view->generateHeaderView([]),
            'content' => $contents,
            'footer' => $this->view->generateFooterView([]),
            // Removing query string from current URL to get its canonical form...
            'canonical' => strtok("https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", '?'),
        ], $params));
    }
}
