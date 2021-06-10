<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Test;
use tcCore\User;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        $tests = Test::filtered($request->get('filter', []), $request->get('order', []))->with('author')->get();
        $authors = [];
        foreach ($tests as $test){
            $authors[] = $test->author;
        }
        $authors = array_unique($authors);
        return Response::make($authors, 200);
    }
}
