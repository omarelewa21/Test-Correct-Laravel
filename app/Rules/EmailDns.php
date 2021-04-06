<?php

namespace tcCore\Rules;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class EmailDns implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $request = new Request();
        $request->merge([$attribute=>$value]);
        $validator = Validator::make($request->all(), [
            'data.*.username' => 'email:dns',
         ]);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'joepie.';
    }
}
