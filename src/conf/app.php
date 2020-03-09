<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

/**
 * In this file you can specify global app configuration.
 */
return [
    'mode' => getenv('ENV'),
    'settings' => [
        'displayErrorDetails' => getenv('ENV') === 'development' ? true : false,
        'outputBuffering' => getenv('ENV') === 'development' ? 'append' : false,
    ],
    // App's language.
    'lang' => 'en',
    // Specifies in which user type to retrieve data from knowledge base.
    'userType' => 0,
    // Specifies in which source to log all user events. Used for filtering in Backstage's dashboard.
    'source' => getenv('ENV') === 'development' ? 'km_debug' : null,
    // Options for Inbenta Authentication API.
    'authentication' => [
        'key' => getenv('INBENTA_API_KEY'),
        'secret' => getenv('INBENTA_API_SECRET'),
    ],
    // Those meta data will be used to fill the HTML page for SEO purpose.
    'metaData' => [
        'keywords' => [],
        'favicon' => 'https://www.inbenta.com/favicon.ico',
        'ogType' => '',
        'ogImage' => '',
        'ogTitle' => '',
        'ogSiteName' => '',
        'shortcutIcon' => '',
    ],
    // Helpsite's sections slugs (base URLs).
    'sectionsSlugs' => [
        'home' => '',
        'categories' => '/categories',
        'contents' => '/contents',
        'results' => '/results',
    ],
    // Those options will be passed to HTML templates in order to tailor pages to your needs.
    'displayOptions' => [
        'categoriesPerLine' => 5,
        'settingsToShow' => ['ANSWER_TEXT'],
        'showRelated' => true,
        'showSearchRating' => false,
        'ratings' => [
            [
                'id' => 1,
                'label' => 'RATINGS_YES',
                'comment' => false,
            ],
            [
                'id' => 2,
                'label' => 'RATINGS_NO',
                'comment' => true,
            ],
        ],
    ],
    // We only allow helpsite indexing in production mode.
    'allowIndexing' => getenv('ENV') === 'production',
    // Custom directory to fetch view templates from. Default directory will be used as a fallback.
    'customTemplatesDirectory' => __DIR__ . '/../Custom/Views/',
    // If a reverse proxy is configured, we use forwarded values...
    'baseUrl' => ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME']) . '://' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']),
];
