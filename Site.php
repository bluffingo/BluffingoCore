<?php

/*
  BluffingoCore

  Copyright (C) 2023-2025 Chaziz

  BluffingoCore is free software: you can redistribute it and/or modify it 
  under the terms of the GNU Affero General Public License as published by 
  the Free Software Foundation, either version 3 of the License, or (at 
  your option) any later version. 

  BluffingoCore is distributed in the hope that it will be useful, but WITHOUT
  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
  FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more 
  details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace BluffingoCore;

use BluffingoCore\Database;
use BluffingoCore\Profiler;
use BluffingoCore\IPLookup;
use BluffingoCore\Localization;

/**
 * class Site
 *
 * The core site class.
 */
class Site
{
    /**
     * @var Database
     */
    protected Database $database;

    /**
     * @var Profiler
     */
    protected Profiler $profiler;

    /**
     * @var IPLookup
     */
    protected ?IPLookup $ip_lookup;

    /**
     * @var Localization
     */
    protected Localization $localization;

    /**
     * @var bool
     */
    private bool $enable_ip_lookup = false;

    /**
     * @var bool
     */
    private bool $is_debug = false;

    /**
     * function __construct
     *
     * Initialize the core Site classes.
     *
     * @param mixed $config
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->database = new Database($config['mysql'] ?? []);
        $this->is_debug = ($config["mode"] ?? '') === "DEV";

        $this->profiler = new Profiler($this->database);
        if ($this->is_debug) {
            // enable db profiler (not to be confused with the other profiler)
            // if we are on debug mode
            $this->database->setProfiling(true);
        }

        $this->localization = new Localization($this->options["locale"] ?? "en-US");

        $this->enable_ip_lookup = $config["ip_lookup"]["enabled"] ?? false;

        if ($this->enable_ip_lookup) {
            $this->ip_lookup = new IPLookup($config["ip_lookup"]["mmdb"]);
        } else {
            $this->ip_lookup = null;
        }
    }

    /**
     * function getDatabaseClass
     *
     * Returns the database class for other classes to use.
     *
     * @return Database
     */
    public function getDatabaseClass(): Database
    {
        return $this->database;
    }

    /**
     * function getProfilerClass
     *
     * Returns the profiler class for other classes to use.
     *
     * @return Profiler
     */
    public function getProfilerClass(): Profiler
    {
        return $this->profiler;
    }

    /**
     * function getLocalizationClass
     *
     * Returns the localization class for other classes to use.
     *
     * @return Localization
     */
    public function getLocalizationClass(): Localization
    {
        return $this->localization;
    }

    /**
     * function isIpLookupEnabled
     *
     * Returns the bool that toggles the IP lookup class.
     *
     * @return bool
     */
    public function isIpLookupEnabled(): bool
    {
        return $this->enable_ip_lookup;
    }

    /**
     * function getIpLookupClass
     *
     * Returns the IP lookup class.
     *
     * @return IPLookup
     */
    public function getIpLookupClass(): IPLookup
    {
        if (!$this->ip_lookup || !$this->enable_ip_lookup) {
            throw new \Exception("getIpLookupClass() called while IP reader is disabled.");
        }
        return $this->ip_lookup;
    }

    /**
     * function isDebug
     *
     * Returns boolean that indicates if debug is enabled.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->is_debug;
    }
}
