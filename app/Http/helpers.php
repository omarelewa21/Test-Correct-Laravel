<?php

use tcCore\Http\Helpers\Settings;
use Illuminate\Support\Facades\Storage;
if (!function_exists('settings')) {
    function settings(): Settings
    {
        return new Settings();
    }
}

function haha(){


    $controller = new \tcCore\Http\Controllers\PreviewTestTakeController();

    $response = $controller->show(tcCore\TestTake::find(39809), new \Illuminate\Http\Request());

    $prefix = \Illuminate\Support\Str::random(10);

    Storage::put('pdf/preview.pdf', $response->getContent());
}