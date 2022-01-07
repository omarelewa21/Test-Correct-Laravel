<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CkeditorImageController extends Controller
{
    public function store(Request $request, $type)
    {

        return [
            "uploaded" => 1,
            "fileName" => "foo.jpg",
            "url"      => route('cms.upload.get', 'uYm8FfLjPW-Collegas-aan-tafel.png')
        ];

    }

    public function show($filename)
    {
        return Storage::disk('cake')->get("questionanswers/$filename");
    }
}
