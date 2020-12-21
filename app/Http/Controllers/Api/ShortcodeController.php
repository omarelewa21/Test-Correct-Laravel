<?php

namespace tcCore\Http\Controllers\Api;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateShortcodeRequest;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;

class ShortcodeController extends Controller
{
    public function registerClickAndRedirect(Request $request, Shortcode $shortcode)
    {
        $redirectUrl = config('shortcode.shortcode.redirect');
        $stitchSign = '?';
        if (substr_count($redirectUrl, '?') > 0) {
            $stitchSign = '&';
        }

        if ($shortcode === null) {
            abort(404);
        }
        $click = ShortcodeClick::create([
            'shortcode_id' => $shortcode->getKey(),
            'ip'           => $_SERVER['REMOTE_ADDR'],
        ]);

        return redirect()->away(sprintf('%s%sref=%s&email=%s', $redirectUrl, $stitchSign, $click->uuid, request('email', '')));
    }

    public function show(Request $request, Shortcode $shortcode)
    {
        if ($shortcode === null) {
            abort(404);
        }

        return response()->json([
            'data' => $shortcode
        ], 200);
    }


    public function store(CreateShortcodeRequest $request)
    {
        try {
            $shortcode = Shortcode::where('user_id', $request->get('user_id'))->first();
            if (!$shortcode) {
                $shortcode = Shortcode::create([
                    'user_id' => $request->get('user_id')
                ]);
            }
            return response()->json([
                'data' => $shortcode
            ], 200);
        } catch (\Throwable $e) {
            abort(500);
        }
    }

}
