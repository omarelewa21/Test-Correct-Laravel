<?php

use tcCore\Http\Helpers\Settings;
use Illuminate\Support\Facades\Storage;
if (!function_exists('settings')) {
    function settings(): Settings
    {
        return new Settings();
    }
}
