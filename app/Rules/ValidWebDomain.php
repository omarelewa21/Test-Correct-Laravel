<?php

namespace tcCore\Rules;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Illuminate\Contracts\Validation\Rule;

class ValidWebDomain implements Rule
{
    protected $failType = 'domain';
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
        $pass = $this->isValidDomainName($value);
        if(!$pass){
            return false;
        }
        $DNSCheckValidation = new DNSCheckValidation();
        $emailLexer = new EmailLexer();
        $result = $DNSCheckValidation->isValid($value,$emailLexer);
        if (!$result) {
            $this->failType = 'dns';
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
        if($this->failType=='dns'){
            return 'Dit domein is valide, maar heeft geen juiste dns voor email';
        }
        return 'Dit domein is niet valide';
    }

    function isValidDomainName($domain_name) {
        return (preg_match("/^([a-zd](-*[a-zd])*)(.([a-zd](-*[a-zd])*))*$/i", $domain_name) //valid characters check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^.]{1,63}(.[^.]{1,63})*$/", $domain_name) ); //length of every label
    }
}
