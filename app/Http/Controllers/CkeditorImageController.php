<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CkeditorImageController extends Controller
{
    public function store(Request $request, $type)
    {
        if($request->hasFile('upload')) {

            if(Validator::make($request->all(), [
                'upload' => [
                    'mimes:png,jpeg,jpg,gif,webp,bmp',
                    'max:' . \tcCore\Http\Helpers\BaseHelper::getMaxFileUploadSize()
                ],
            ])->fails()) {
                return [
                    "uploaded" => 0,
                    "error" => [
                        "message" => __('cms.ckeditor_file_type_not_allowed')
                    ]
                ];
            }

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
