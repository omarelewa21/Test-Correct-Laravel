<?php

use Illuminate\Support\Js;
use Illuminate\Http\Request;
use tcCore\Http\Controllers\PreviewTestTakeController;
use tcCore\Http\Helpers\Settings;
use Illuminate\Support\Facades\Storage;
use tcCore\TestTake;

if (!function_exists('settings')) {
    function settings(): Settings
    {
        return new Settings();
    }
}

if (!function_exists('js')) {
    /* The @js blade helper doesn't work when used on a blade component
        <x-icon.edit class="@js($property)"/> -> breaks
    So this helper encapsulates the same logic but can be used within {{ ... }}
        <x-icon.edit class="{{ js($property) }}"/> -> works now
    */
    function js($data): Js
    {
        return new Js($data);
    }
}

if(!function_exists('pdf_answers_for_testtake')){
    function pdf_answers_for_testtake($testTakeId) {
        $testTake = \tcCore\TestTake::findOrFail($testTakeId);

        (new PreviewTestTakeController)->show($testTake, new Request(), false);
    }
}