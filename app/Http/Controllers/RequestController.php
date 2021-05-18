<?php

namespace tcCore\Http\Controllers;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    use MakesHttpRequests;

    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

}
