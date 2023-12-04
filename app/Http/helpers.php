<?php

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

if(!function_exists('pdf_answers_for_testtake')){
    function pdf_answers_for_testtake($testTakeId) {
        $testTake = \tcCore\TestTake::findOrFail($testTakeId);
        (new PreviewTestTakeController)->show($testTake, new Request(), false);
    }
}