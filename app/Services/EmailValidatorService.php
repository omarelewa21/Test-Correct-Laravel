<?php namespace tcCore\Services;
;

class EmailValidatorService
{
    private $domains = [];

    private $mail;


    public function __construct($strDomain, $mail) {
        $this->domains = explode(';', $strDomain);
        $this->mail = $mail;
    }

    public function passes() {
        return false;
    }

    public function getMessage() {
        return '';
    }



}
