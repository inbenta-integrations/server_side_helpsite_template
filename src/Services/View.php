<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Services;

use Slim\Http\Response;
use Slim\Views\PhpRenderer;

/**
 * Handles view generation from HTML templates.
 */
class View
{
    /** @var array Global parameters to pass to templates. */
    private $globalParams;

    /** @var array Custom assets paths to include into the current page. */
    private $customAssets;

    /** @var string Path to custom templates directory to use. */
    private $customTemplatesDirectory;

    /** @var \Slim\Views\PhpRenderer Default view renderer service that will be used as a fallback. */
    private $defaultPhpRenderer;

    /** @var \Slim\Views\PhpRenderer View renderer service instance. */
    private $phpRenderer;

    /**
     * Class constructor.
     *
     * @param string [$customTemplatesDirectory = ''] Custom templates directory path.
     *
     * @return void
     */
    public function __construct(string $customTemplatesDirectory = __DIR__ . '/../Views/')
    {
        $this->globalParams = [];
        $this->customAssets = [];
        $this->customTemplatesDirectory = $customTemplatesDirectory;
        $this->phpRenderer = new \Slim\Views\PhpRenderer($customTemplatesDirectory);
        $this->defaultPhpRenderer = new \Slim\Views\PhpRenderer(__DIR__ . '/../Views/');
    }

    /**
     * Defines custom assets paths to include into the current page.
     *
     * @param array $customAssets Custom assets paths to include into the current page.
     * Paths must be relative to the `public` directory.
     *
     * @return void
     */
    public function setCustomAssets(array $customAssets): void
    {
        $this->customAssets = $this->customAssets = $customAssets;
    }

    /**
     * Defines global parameters to automatically pass to each template.
     *
     * @param array $parameters Global parameters to pass to each template.
     *
     * @return void
     */
    public function setGlobalParams(array $parameters): void
    {
        $this->globalParams = array_merge($this->globalParams, $parameters);
    }

    /**
     * Generates search box view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateSearchBoxView(array $values): string
    {
        return $this->fetch('search-box.html', [
            'action' => $values['action'],
        ]);
    }

    /**
     * Generates list of push and popular contents view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generatePushAndPopularView(array $values): string
    {
        return $this->fetch('push-popular.html', [
            'pushPopularResults' => $values['pushPopularResults'],
        ]);
    }

    /**
     * Generates list of push contents view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generatePushView(array $values): string
    {
        return $this->fetch('push.html', [
            'pushResults' => $values['pushResults'],
        ]);
    }

    /**
     * Generates list of popular contents view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generatePopularView(array $values): string
    {
        return $this->fetch('popular.html', [
            'popularResults' => $values['popularResults'],
        ]);
    }

    /**
     * Generates list of last updated contents view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateLastUpdatedContentsView(array $values): string
    {
        return $this->fetch('last-created-contents.html', [
            'contents' => $values['contents'],
        ]);
    }

    /**
     * Generates list of categories view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoriesView(array $values): string
    {
        return $this->fetch('categories.html', [
            'backUrl' => $values['backUrl'],
            'baseCategory' => $values['baseCategory'],
            'subCategories' => $values['subCategories'],
            'categoryContents' => $values['categoryContents'],
        ]);
    }

    /**
     * Generates category view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoryView(array $values): string
    {
        return $this->fetch('category.html', [
            'contents' => $values['contents'],
            'category' => $values['category'],
        ]);
    }

    /**
     * Generates category's popular contents view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoryPopularsView(array $values): string
    {
        return $this->fetch('category-popular.html', [
            'contents' => $values['contents'],
            'category' => $values['category'],
            'intro' => $values['introduction'],
        ]);
    }

    /**
     * Generates categories sidebar view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoriesSidebarView(array $values): string
    {
        return $this->fetch('categories-sidebar.html', [
            'category' => $values['categoryTree'],
            'currentSlug' => $values['currentSlug'],
        ]);
    }

    /**
     * Generates content view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateContentView(array $values): string
    {
        return $this->fetch('results.html', [
            'lastQuery' => '',
            'searchRateCode' => '',
            'displayTitle' => false,
            'results' => $values['results'],
            'backUrl' => $values['backUrl'],
        ]);
    }

    /**
     * Generates results view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateResultsView(array $values): string
    {
        return $this->fetch('results.html', [
            'backUrl' => $values['backUrl'],
            'results' => $values['results'],
            'lastQuery' => $values['lastQuery'],
            'searchRateCode' => $values['searchRateCode'],
            'displayTitle' => true,
        ]);
    }

    /**
     * Generates sitemap view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateSitemapView(array $values): string
    {
        return $this->fetch('sitemap.xml', [
            'contents' => $values['contents'],
        ]);
    }

    /**
     * Generates robots.txt view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateRobotsView(array $values): string
    {
        return $this->fetch('robots.txt', [
            'robotRule' => $values['robotRule'],
            'sitemapUrl' => $values['sitemapUrl'],
        ]);
    }

    /**
     * Generates home banner view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateHomeBannerView(array $values): string
    {
        return $this->fetch('home-banner.html', []);
    }

    /**
     * Generates category banner view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoryBannerView(array $values): string
    {
        return $this->fetch('category-banner.html', []);
    }

    /**
     * Generates results banner view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateResultsBannerView(array $values): string
    {
        return $this->fetch('results-banner.html', []);
    }

    /**
     * Generates content banner view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateContentBannerView(array $values): string
    {
        return $this->fetch('content-banner.html', []);
    }

    /**
     * Generates categories breadcrumb view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateCategoriesBreadcrumbView(array $values): string
    {
        // var_dump($values);exit();
        return $this->fetch('categories-breadcrumb.html', [
            'breadcrumb' => $values['breadcrumb'],
        ]);
    }

    /**
     * Generates not found error view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateNotFoundErrorView(array $values): string
    {
        return $this->fetch('404.html', []);
    }

    /**
     * Generates internal error view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateErrorView(array $values): string
    {
        return $this->fetch('500.html', []);
    }

    /**
     * Generates base HTML page view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateBaseView(array $values): string
    {
        return $this->fetch('base.html', [
            'breadcrumb' => $values['breadcrumb'],
        ]);
    }

    /**
     * Generates header view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateHeaderView(array $values): string
    {
        return $this->fetch('header.html', $values);
    }

    /**
     * Generates footer view.
     *
     * @param array $values Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function generateFooterView(array $values): string
    {
        return $this->fetch('footer.html', $values);
    }

    /**
     * Generates view from custom or default template file, depending on whether custom one exists.
     *
     * @param string $templateFileName Template file name.
     *
     * @param array [$values = []] Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function fetch(string $templateFileName, array $values = []): string
    {
        $completeValues = array_merge($this->globalParams, [
            'fetch' => [$this, 'fetch'],
            'sanitize' => [$this, 'sanitize'],
            'normalize' => [$this, 'normalize'],
        ], $values);
        if (!file_exists($this->customTemplatesDirectory . $templateFileName)) {
            return $this->defaultPhpRenderer->fetch($templateFileName, $completeValues);
        }
        return $this->phpRenderer->fetch($templateFileName, $completeValues);
    }

    /**
     * Sanitizes the given input, to prevent any security issue.
     *
     * @param string $templateFileName Template file name.
     *
     * @param array [$values = []] Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function sanitize(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Normalizes the given string, tranforming it into a slug.
     *
     * @param string $templateFileName Template file name.
     *
     * @param array [$values = []] Values used to fill the template.
     *
     * @return string Generated view.
     */
    public function normalize(string $input): string
    {
        return preg_replace('/\s/', '-', strtolower($input));
    }

    /**
     * Renders a full page with given content.
     *
     * @param \Slim\Http\Response $response HTTP response to fill.
     *
     * @param string $template HTML template to use as a base structure.
     *
     * @param array $values Values used to fill the base template.
     *
     * @return \Slim\Http\Response Filled HTTP response.
     */
    public function render(Response $response, string $template, array $values): Response
    {
        $completeValues = array_merge($this->globalParams, [
            'fetch' => [$this, 'fetch'],
            'sanitize' => [$this, 'sanitize'],
            'normalize' => [$this, 'normalize'],
            'customAssets' => $this->customAssets,
        ], $values);

        // Using labels as fallback if no title nor description is given...
        if (!isset($completeValues['title'])) {
            $completeValues['title'] = $this->globalParams['translate']('PAGE_TITLE');
        }
        if (!isset($completeValues['description'])) {
            $completeValues['description'] = $this->globalParams['translate']('PAGE_DESCRIPTION');
        }

        if (!file_exists($this->customTemplatesDirectory . $template)) {
            return $this->defaultPhpRenderer->render($response, $template, $completeValues);
        }
        return $this->phpRenderer->render($response, $template, $completeValues);
    }
}
