<?php

/*
  BluffingoCore

  Copyright (C) 2021-2025 Chaziz
  Copyright (C) 2021-2023 ROllerozxa

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

use Exception;
use PDO;

/**
 * class Database
 *
 * Simplified PDO interface.
 */
class Database
{
    /**
     * @var PDO
     */
    private PDO $sql;

    /**
     * @var array
     */
    private array $queryLog = [];

    /**
     * @var bool
     */
    private bool $profilingEnabled = false;

    /**
     * function __construct
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $host = $config["host"] ?? "";
        $db = $config["database"] ?? "";
        $user = $config["username"] ?? "";
        $pass = $config["password"] ?? "";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"'
        ];

        $this->sql = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, $options);
    }

    /**
     * function result
     *
     * @param mixed $query
     * @param mixed $params
     *
     * @return mixed
     */
    public function result($query, $params = [])
    {
        $res = $this->query($query, $params);
        return $res->fetchColumn();
    }

    /**
     * function query
     *
     * @param mixed $query
     * @param mixed $params
     *
     * @return mixed
     */
    public function query($query, $params = [])
    {
        $startTime = 0;
        $executionTime = 0;

        if ($this->profilingEnabled) {
            $startTime = microtime(true);
        }

        $res = $this->sql->prepare($query);
        $res->execute($params);

        if ($this->profilingEnabled) {
            $executionTime = microtime(true) - $startTime;

            $this->logQueryForProfiler($query, $params, $startTime, $executionTime);
        }

        return $res;
    }

    /**
     * function fetchArray
     *
     * @param mixed $query
     *
     * @return array
     */
    public function fetchArray($query): array
    {
        $out = [];
        while ($record = $query->fetch()) {
            $out[] = $record;
        }
        return $out;
    }

    /**
     * function fetch
     *
     * @param mixed $query
     * @param mixed $params
     *
     * @return mixed
     */
    public function fetch($query, $params = [])
    {
        $res = $this->query($query, $params);
        return $res->fetch();
    }

    /**
     * function insertId
     *
     * @return mixed
     */
    public function insertId()
    {
        return $this->sql->lastInsertId();
    }

    /**
     * function insertInto
     *
     * Helper function to insert a row into a table.
     *
     * @param mixed $table
     * @param mixed $data
     * @param mixed $dry
     *
     * @return mixed
     */
    public function insertInto($table, $data, $dry = false)
    {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }

        /*
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
        $table, commasep($fields), commasep($placeholders));
        */

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(',', $fields),
            implode(',', $placeholders)
        );

        if ($dry)
            return $query;
        else
            return $this->query($query, $values);
    }

    /**
     * function updateRowQuery
     *
     * Helper function to construct part of a query to set a lot of fields in one row
     *
     * @param mixed $fields
     *
     * @return array
     */
    public function updateRowQuery($fields)
    {
        // Temp variables for dynamic query construction.
        $fieldquery = '';
        $placeholders = [];

        // Construct a query containing all fields.
        foreach ($fields as $fieldk => $fieldv) {
            if ($fieldquery) $fieldquery .= ',';
            $fieldquery .= $fieldk . '=?';
            $placeholders[] = $fieldv;
        }

        return ['fieldquery' => $fieldquery, 'placeholders' => $placeholders];
    }

    /**
     * function paginate
     *
     * @param mixed $page
     * @param mixed $pp
     *
     * @return mixed
     */
    public function paginate($page, $pp)
    {
        $page = (is_numeric($page) && $page > 0 ? $page : 1);

        // if its too high just set it back to 1 to avoid a database error.
        // THIS IS BY DESIGN. -chaziz 9/13/2025
        if ($page > 2147483647) {
            $page = 1;
        }

        return sprintf(" LIMIT %s, %s", (($page - 1) * $pp), $pp);
    }

    /**
     * function getServerVersion
     *
     * @return mixed
     */
    public function getServerVersion()
    {
        return $this->sql->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * function logQueryForProfiler
     *
     * @param mixed $query
     * @param mixed $params
     * @param mixed $startTime
     * @param mixed $executionTime
     *
     * @return void
     */
    private function logQueryForProfiler($query, $params, $startTime, $executionTime)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $immediateCaller = $backtrace[0] ?? [];
        $actualCaller = $backtrace[1] ?? [];

        // check if the caller isnt right here for queries done through fetch and fetchArray
        $caller = (str_ends_with($immediateCaller['file'] ?? '', 'Database.php'))
            ? $actualCaller
            : $immediateCaller;

        // remove root path so we have a shorter string
        $file = str_replace(BLUFF_ROOT_PATH, '', $caller['file'] ?? '');

        $callerInfo = [
            'file' => $file ?? 'unknown',
            'line' => $caller['line'] ?? 'unknown',
            'function' => $caller['function'] ?? 'unknown',
        ];

        $this->queryLog[] = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true),
            'caller_info' => $callerInfo,
        ];
    }

    /**
     * function setProfiling
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function setProfiling(bool $enabled): void
    {
        $this->profilingEnabled = $enabled;
    }

    /**
     * function getQueryLog
     *
     * @return array
     */
    public function getQueryLog(): array
    {
        if (!$this->profilingEnabled) {
            return [];
        }

        return $this->queryLog;
    }

    /**
     * function getProfilingReport
     *
     * IMPORTANT: DO NOT CALL THIS FUNCTION OUTSIDE OF PROFILER. IF YOU NEED THE DATABASE PROFILING REPORT.
     * GET THAT SHIT THROUGH THE PROFILER CLASS' getDatabaseProfilerInfo FUNCTION (because then youll get
     * the full data). -chaziz -4/12/2025
     *
     * @return array
     */
    public function getProfilingReport(): array
    {
        if (!$this->profilingEnabled) {
            return [];
        }

        $report = [
            'total_queries' => count($this->queryLog),
            'total_time' => 0,
            'queries' => [],
            'slowest_query' => null,
            'fastest_query' => null,
        ];

        if (empty($this->queryLog)) {
            return $report;
        }

        $slowest = $this->queryLog[0];
        $fastest = $this->queryLog[0];

        foreach ($this->queryLog as $query) {
            $report['total_time'] += $query['execution_time'];

            // find the slowest and fastest queries
            if ($query['execution_time'] > $slowest['execution_time']) {
                $slowest = $query;
            }

            if ($query['execution_time'] < $fastest['execution_time']) {
                $fastest = $query;
            }

            $report['queries'][] = [
                'query' => $query['query'],
                'time' => $query['execution_time'],
                'params' => $query['params'],
                'caller_info' => $query['caller_info'],
            ];
        }

        $report['slowest_query'] = $slowest;
        $report['fastest_query'] = $fastest;
        $report['average_time'] = $report['total_time'] / $report['total_queries'];

        return $report;
    }
}
