<?php 

namespace tcCore\Http\Controllers;

use tcCore\Http\Requests\CreateGetConfigRequest;
use Illuminate\Support\Facades\Response;

class ConfigController extends Controller
{
    
    public function show(CreateGetConfigRequest $request)
    {

        if($request['laravel_config_variable'] == 'shortcode.shortcode.redirect') 
        {
        
        $return = config('shortcode.shortcode.redirect');
        
        return Response::make(['status' => $return], 200);
        
        }
        
        return Response::make(['status' => ''], 200);
        
    }

}
