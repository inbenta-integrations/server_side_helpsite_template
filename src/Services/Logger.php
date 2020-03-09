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
 * Provides function for logging messages in development mode.
 */
class Logger
{
    /** @var string App mode (development, preproduction, production). */
    private $mode;

    /** @var string Prefix put before every logged message. */
    private $prefix;

    /**
     * Class constructor.
     *
     * @param string $mode App mode.
     *
     * @param string $prefix Prefix to put before every logged message.
     *
     * @return void
     */
    public function __construct(string $mode, string $prefix)
    {
        $this->mode = $mode;
        $this->prefix = $prefix;
    }

    /**
     * Logs a message in standard error output in development mode.
     *
     * @param string $message Message to log.
     *
     * @return void
     */
    public function log($message): void
    {
        if ($this->mode === 'development') {
            error_log($this->prefix . ' - ' . $message);
        }
    }
}
