<?php

/*
  BluffingoCore

  Copyright (C) 2025 Chaziz

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
 * Static core utilities.
 */
class CoreUtilities
{
    public static function getURL(bool $includeURI = false): ?string
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return null;
        }

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];

        if ($includeURI && isset($_SERVER['REQUEST_URI'])) {
            return $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
        }

        return $protocol . '://' . $host;
    }

    public static function redirect(string $url, int $statusCode = 302): never
    {
        header("Location: $url", true, $statusCode);
        exit;
    }
}
