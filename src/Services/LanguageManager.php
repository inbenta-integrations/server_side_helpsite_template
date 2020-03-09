<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Services;

/**
 * Handles application labels translation in available languages.
 */
class LanguageManager
{
    /** @var string Helpsite's translation language. */
    private $lang;

    /** @var array Application's labels. */
    private $labels;

    /** @var array Categories metadata (title, description, introduction). */
    private $categoriesMetadata;

    /**
     * Class constructor.
     *
     * @param string $lang Id of the language to pick labels from (`en`, `fr`, ...).
     *
     * @return void
     */
    public function __construct(string $lang)
    {
        $this->lang = $lang;
    }

    /**
     * Returns current translation language.
     *
     * @return string The current transltion language.
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Defines all the application's labels.
     *
     * @param array $labels List of all the application's labels.
     *
     * @return void
     */
    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * Defines all the application's categories metadata.
     *
     * @param array $categoriesMetadata Categories metadata (title, description, introduction).
     *
     * @return void
     */
    public function setCategoriesMetadata(array $categoriesMetadata): void
    {
        $this->categoriesMetadata = $categoriesMetadata;
    }

    /**
     * Returns translation for the given label.
     *
     * @param string $labelName Label's name.
     *
     * @return string Label's translation if exists, label's name otherwise.
     */
    public function translate(string $labelName): string
    {
        if (isset($this->labels[$labelName])) {
            return $this->labels[$labelName];
        }
        return $labelName;
    }

    /**
     * Returns metadata for the given category.
     *
     * @param string $categoryName Category name.
     *
     * @return array Category's metadata.
     */
    public function getCategoryMetadata(string $categoryName): array
    {
        foreach ($this->categoriesMetadata as $categoryMetadata) {
            if ($categoryMetadata['name'] === $categoryName) {
                return $categoryMetadata;
            }
        }
        return [
            'title' => $categoryName,
            'description' => $categoryName,
            'introduction' => $categoryName,
        ];
    }
}
