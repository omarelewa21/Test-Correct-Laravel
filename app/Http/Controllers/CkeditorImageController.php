<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CkeditorImageController extends Controller
{
    public function store(Request $request, $type)
    {
        if($request->hasFile('upload')) {

            $request->file('upload')->store('', 'inline_images');

            return [
                "uploaded" => 1,
                "fileName" => $request->file('upload')->hashName(),
                "url"      => route('inline-image', $request->file('upload')->hashName(), false)
            ];
        }

        return [
            "uploaded" => 0,
            "error" => [
                "message" => "Something went wrong"
            ]
        ];

    }
}
