<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class TestingController extends Controller {

	/**
	 * reset database tot test
	 *
	 * @return Response
	 */
	public function refreshdb(Request $request)
	{
	    if(config('app.env') != 'local'){
            return Response::make('not allowed', 500);
        }

	    Artisan::call('test:refreshdb');
		return Response::make('OK', 200);
	}

}
