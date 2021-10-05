<?php

namespace tcCore\Http\Helpers;

use tcCore\TestTakeCode;

class TestCodeHelper extends BaseHelper
{

    public function __construct()
    {

    }

    public function validateCode($testTakeCode)
    {
        $code = implode('',$testTakeCode);

        $testTakeCode = TestTakeCode::whereCode($code)->first();

        if (!$testTakeCode) {
            return false;
        }
    }
}