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
 * class Branding
 */
class Branding
{
    private $brand_settings = [
        "name" => "BrandName",
        "assets" => "/assets/placeholder",
        "is_vector" => false,
        "use_wordmark" => false,
    ];

    /**
     * function __construct
     * 
     * Initialize Branding class.
     */
    public function __construct(array $settings)
    {
        $this->brand_settings = $settings;
    }

    /**
     * function setBrandingSettings
     * 
     * Set branding settings. This is used on SquareBracket to switch 
     * from SquareBracket branding to FulpTube branding whenever the user is
     * either on the Finalium Hitchhiker theme or on the FulpTube.rocks domain.
     */
    public function setBrandingSettings(array $settings)
    {
        $this->brand_settings = array_merge($this->brand_settings, $settings);
    }

    /**
     * function getBrandSettings
     * 
     * Get branding settings.
     */
    public function getBrandSettings()
    {
        return $this->brand_settings;
    }

    // todo: add functions to get specific branding settings
}
