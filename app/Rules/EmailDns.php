<?php

namespace tcCore\Rules;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Validation\DNSCheckValidation;

class EmailDns implements Rule
{
    private $attribute;
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
        $this->attribute  = $attribute;
        $DNSCheckValidation = new DNSCheckValidation();
        $emailLexer = new EmailLexer();
        $result = $DNSCheckValidation->isValid($value,$emailLexer);
        if (!$result) {
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
        return $this->attribute. ' email failed on dns';
    }
}
