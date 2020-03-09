<?php

/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

namespace Inbenta\ServerSideHelpsite\Utils;

/**
 * Caching system that allows to locally cache date to improve response times and performance.
 */
class FileCached
{
    /** @var array Contains all cached data. */
    private $cache;

    /** @var string Cache file path. */
    private $cacheFilePath;

    /**
     * Class constructor.
     *
     * @param string Cache file path.
     *
     * @return void
     */
    public function __construct(string $cacheFilePath)
    {
        $this->cacheFilePath = $cacheFilePath;
        if (!file_exists($cacheFilePath)) {
            $this->cache = [];
        } else {
            $this->cache = json_decode(file_get_contents($cacheFilePath), true);
        }
    }

    /**
     * Retrieves data from cache by the given key, if exist.
     *
     * @param string $key Data key.
     *
     * @return mixed Cached data if exist, `false` otherwise.
     */
    public function get(string $key)
    {
        if (!isset($this->cache[$key])) {
            return false;
        }
        return $this->cache[$key];
    }

    /**
     * Stores data into cache with the given key.
     *
     * @param string $key Data key.
     *
     * @param mixed $value Data to store in cache.
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        $this->cache[$key] = $value;
        file_put_contents($this->cacheFilePath, json_encode($this->cache));
    }
}
