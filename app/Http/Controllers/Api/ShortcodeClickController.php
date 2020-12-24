<?php

namespace tcCore\Http\Controllers\Api;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\UpdateShortcodeClickRequest;
use tcCore\Shortcode;
use tcCore\ShortcodeClick;

class ShortcodeClickController extends Controller
{

    public function update(UpdateShortcodeClickRequest $request, ShortcodeClick $shortcodeClick)
    {
        $shortcodeClick->fill($request->validated());
        $shortcodeClick->save();

        return response()->json([

        ],200);
    }

}
