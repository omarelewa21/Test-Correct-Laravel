<?php

use tcCore\Http\Helpers\Settings;

if (!function_exists('settings')) {
    function settings(): Settings
    {
        return new Settings();
    }
}