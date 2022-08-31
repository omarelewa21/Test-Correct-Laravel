<?php 

namespace tcCore\Http\Controllers;

use tcCore\Http\Requests\ShowGetConfigRequest;
use Illuminate\Support\Facades\Response;

class ConfigController extends Controller
{
    
    public function show(ShowGetConfigRequest $request)
    {

        $allowed = ['shortcode.shortcode.redirect', 'custom.default_trial_days'];

        if(in_array($request['laravel_config_variable'],$allowed))
        {
            $return = config($request['laravel_config_variable']);
        
            return Response::make(['status' => $return], 200);
        
        }
        
        return Response::make(['status' => ''], 403);
        
    }

}
