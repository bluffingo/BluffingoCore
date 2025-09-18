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

use Exception;

class CoreVersionNumber
{
    private string $versionNumber;
    private string $versionString;

    public function __construct()
    {
        $this->versionNumber = "1.0.0-rc.1";
        $this->versionString = $this->makeVersionString();
    }

    /**
     * Make BluffingoCore's version number.
     */
    private function makeVersionString(): string
    {
        try {
            $gitInfo = new GitInfo(BLUFF_PRIVATE_PATH . "/class/BluffingoCore");

            $branch = $gitInfo->getGitBranch();
            $hash = $gitInfo->getGitCommitHash();

            // if for example, the version number is bluffingocore 1.0 and
            // we're on the core-1.0 branch, we don't need to show the git 
            // branch as it would just repeat itself.
            if (preg_match('/^(\d+\.\d+)/', $this->versionNumber, $matches)) {
                $majorMinor = $matches[1];

                if (str_starts_with($branch, 'core-' . $majorMinor)) {
                    return sprintf('%s-%s', $this->versionNumber, $hash);
                }
            }

            return sprintf('%s.%s-%s', $this->versionNumber, $branch, $hash);
        } catch (Exception) {
            return $this->versionNumber;
        }

        return $this->versionNumber;
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
