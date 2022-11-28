<?php namespace tcCore\Services;

;

class EmailValidatorService
{
    private $domains = [];

    private $mail;

    private $messages = [];


    public function __construct($strDomain, $mail)
    {
        $this->domains = explode(';', $strDomain);
        $this->mail = $mail;
    }

    public function passes()
    {
        $fail = true;
        foreach ($this->domains as $domain) {
            if (!str_starts_with($domain, '*') && !str_starts_with($domain, '.') && !str_starts_with($domain, '@')) {
                $domain = '@'.$domain;
            }

            if (str_starts_with($domain, '*')) {
                $domain = substr($domain, 1);
            }
            if (str_ends_with($this->mail, $domain)) {
                $fail = false;
            }
        }
        return !$fail;
    }

    public function getMessage()
    {
        if ($this->passes()) {
            return '';
        }

        $messages = [];
        foreach ($this->domains as $domain) {
            if (!str_starts_with($domain, '*') && !str_starts_with($domain, '.') && !str_starts_with($domain, '@')) {
                $domain = '@'.$domain;
            }

            if (str_starts_with($domain, '*')) {
                $domain = substr($domain, 1);
                if (str_starts_with($domain, '.')) {
                    $messages[] = $domain;
                } elseif (str_starts_with($domain, '@')) {
                    $messages[] = $domain;
                } else {
                    $messages[] = "*".$domain;
                }
            } else {
                if (str_starts_with($domain, '.')) {
                    $messages[] = $domain;
                } elseif (str_starts_with($domain, '@')) {
                    $messages[] = $domain;
                } else {
                    $messages[] = "@".$domain;
                }
            }
        }

        return $messages;
    }


}
