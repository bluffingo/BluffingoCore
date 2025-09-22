<?php

/*
  BluffingoCore

  Copyright (C) 2021 ROllerozxa
  Copyright (C) 2021-2022 icanttellyou
  Copyright (C) 2021-2025 Chaziz

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

/**
 * class Profiler
 *
 * Revamped profiler.
 * NOTE: The database query profiler is in the Database class.
 */
class Profiler
{
    /**
     * @var Database
     */
    private Database $database;

    /**
     * @var mixed
     */
    private $starttime;

    /**
     * @var mixed
     */
    private $database_query_log;

    /**
     * @var array
     */
    private ?array $database_profiling_report;

    /**
     * @var bool
     */
    private bool $database_profiler_function_called = false;

    /**
     * function __construct
     *
     * @param mixed $database
     *
     * @return void
     */
    public function __construct($database)
    {
        $this->database = $database;
        $this->starttime = microtime(true);
    }

    // this should be called AFTER the database is done with everything

    /**
     * function getDatabaseProfilerInfo
     *
     * @return void
     */
    private function getDatabaseProfilerInfo(): void
    {
        // slightly ugly hack so we dont repeat this shit (because of squarebrackettwigextension)
        if (!$this->database_profiler_function_called) {
            $this->database_profiler_function_called = true;
            $this->database_query_log = $this->database->getQueryLog();
            $this->database_profiling_report  = $this->database->getProfilingReport();
        }
    }

    /**
     * function whoAmI
     *
     * @return string
     */
    private function whoAmI(): string
    {
        $whoami = exec('whoami');
        if ($whoami) {
            return "Running under system user " . $whoami;
        }
        return "Running under unknown system user";
    }

    /**
     * function getStats
     *
     * @return void
     */
    public function getStats(): void
    {
        $this->getDatabaseProfilerInfo();

        printf(
            "Rendered in %1.6fs with %dKB memory used. %s.",
            microtime(true) - $this->starttime,
            memory_get_usage() / 1024,
            $this->whoAmI()
        );
    }

    /**
     * function getDatabaseProfilingReport
     *
     * @return array
     */
    public function getDatabaseProfilingReport(): ?array
    {
        $this->getDatabaseProfilerInfo();

        return $this->database_profiling_report;
    }
}
