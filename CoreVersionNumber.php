<?php

/*
  BluffingoCore

  Copyright (C) 2025 Chaziz

  OpenSB is free software: you can redistribute it and/or modify it under the 
  terms of the GNU Affero General Public License as published by the Free 
  Software Foundation, either version 3 of the License, or (at your option) any
  later version. 

  OpenSB is distributed in the hope that it will be useful, but WITHOUT ANY 
  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
  FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more 
  details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace BluffingoCore;

class CoreVersionNumber
{
    private string $versionNumber;
    private string $versionString;

    public function __construct()
    {
        $this->versionNumber = "0.0.0";
        $this->versionString = $this->makeVersionString();
    }

    /**
     * Make BluffingoCore's version number.
     */
    private function makeVersionString(): string
    {
        // now i'm aware this could be unreliable. but i don't care.
        // -chaziz 7/17/2025

        $gitHeadLocation = '';

        $coreSubmodulePath = SB_PRIVATE_PATH . "/class/BluffingoCore";

        $gitFile = $coreSubmodulePath . '/.git';

        if (!file_exists($gitFile)) {
            return $this->versionNumber;
        }

        $content = file_get_contents($gitFile);

        if (preg_match('/^gitdir: (.*)$/m', $content, $matches)) {
            $gitDir = trim($matches[1]);
            // handle relative paths
            if ($gitDir[0] !== '/') {
                $gitDir = $coreSubmodulePath . '/' . $gitDir;
            }
            $gitHeadLocation = $gitDir . '/HEAD';

            $gitHead = file_get_contents($gitHeadLocation);
            $gitBranch = rtrim(preg_replace("/(.*?\/){2}/", '', $gitHead));
            $commit = file_get_contents($gitDir . '/refs/heads/' . $gitBranch); // kind of bad but hey it works

            $hash = substr($commit, 0, 7);

            return sprintf('%s.%s-%s', $this->versionNumber, $gitBranch, $hash);
        } else {
            return $this->versionNumber;
        }
    }

    /**
     * Returns the version number.
     *
     * @return string
     */
    public function getVersionNumber(): string
    {
        return $this->versionNumber;
    }

    /**
     * Returns the version string.
     *
     * @return string
     */
    public function getVersionString(): string
    {
        return $this->versionString;
    }
}
