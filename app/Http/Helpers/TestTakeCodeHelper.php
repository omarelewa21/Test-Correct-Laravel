<?php

namespace tcCore\Http\Helpers;

use tcCore\TestTakeCode;
use tcCore\User;

class TestTakeCodeHelper extends BaseHelper
{

    public function __construct()
    {

    }

    public function getTestTakeCodeIfExists($testTakeCode)
    {
        $code = is_array($testTakeCode) ? implode('', $testTakeCode) : $testTakeCode;

        return TestTakeCode::whereCode($code)->first();
    }

    public function createUserByTestTakeCode(TestTakeCode $testTakeCode)
    {
        dd('Nieuwe user maken');
    }
}