<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Middlewares;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handles application's labels and metadata.
 */
class Language
{
    /** @var \Inbenta\ServerSideHelpsite\Services\Logger Logging service. */
    private $logger;

    /** @var \Object Caching client. */
    private $cacheClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\KmApiClient Inbenta KM API client. */
    private $kmApiClient;

    /** @var \Inbenta\ServerSideHelpsite\Services\LanguageManager Labels translation service. */
    private $languageManager;

    /**
     * Loads labels from Backstage's extra info.
     *
     * @return array Loaded labels.
     */
    private function loadLabelsFromExtraInfo()
    {
        if ($this->cacheClient->get('labels') === false) {
            $this->logger->log('Storing labels in cache...');
            $labels = array_reduce($this->kmApiClient->getAppData([
                'dataId' => 'app_data',
                'name' => 'labels',
            ])['results'][0]['value']['labels'] ?? [], function ($labels, $label) {
                return array_merge($labels, [$label['name'] => $label['value']]);
            }, []);
            $this->cacheClient->set('labels', $labels, 1800);
        } else {
            $this->logger->log('Using cached labels...');
        }

        return $this->cacheClient->get('labels');
    }

    /**
     * Loads categories metadata from Backstage's extra info.
     *
     * @return array Loaded categories metadata.
     */
    private function loadCategoriesMetadataFromExtraInfo()
    {
        if ($this->cacheClient->get('categories_metadata') === false) {
            $this->logger->log('Storing categories_metadata in cache...');
            $this->cacheClient->set('categories_metadata', $this->kmApiClient->getAppData([
                'dataId' => 'app_data',
                'name' => 'categories_metadata',
            ])['results'][0]['value']['categories_metadata'] ?? [], 1800);
        } else {
            $this->logger->log('Using cached categories_metadata...');
        }
        return $this->cacheClient->get('categories_metadata');
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
        $this->logger = $container['logger'];
        $this->cacheClient = $container['cache'];
        $this->kmApiClient = $container['kmApiClient'];
        $this->languageManager = $container['languageManager'];
    }

    /**
     * Loads all necessary labels and metadata from both local files and extra info.
     *
     * @param \Slim\Http\Request $request  HTTP request.
     *
     * @param \Slim\Http\Response $response HTTP response.
     *
     * @param \callable $next Next middleware to call.
     *
     * @return \Slim\Http\Response Updated HTTP response for the next handler.
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $lang = $this->languageManager->getLang();

        // Loading labels and metadata from files first, to use them as a fallback...
        $categoriesMetadataFilePath = __DIR__ . '/../locale/' . $lang . '/categories.json';
        $labelsFilePath = __DIR__ . '/../locale/' . $lang . '/labels.json';
        if (!file_exists($categoriesMetadataFilePath) || !file_exists($labelsFilePath)) {
            throw new \Exception('Could not load labels for language "' . $lang . '": files do not exist.');
        }
        $labels = json_decode(file_get_contents($labelsFilePath), true);
        $categoriesMetadata = json_decode(file_get_contents($categoriesMetadataFilePath), true);
        if (is_null($labels) || is_null($categoriesMetadata)) {
            throw new \Exception('Could not load labels for ' . $lang . ': bad files format.');
        }

        // Loading labels and metadata from Backstage's extra info, if present...
        $labels = array_merge($labels, $this->loadLabelsFromExtraInfo());
        $categoriesMetadata = array_merge($categoriesMetadata, $this->loadCategoriesMetadataFromExtraInfo());

        $this->languageManager->setLabels($labels);
        $this->languageManager->setCategoriesMetadata($categoriesMetadata);
        return $next($request, $response);
    }
}
