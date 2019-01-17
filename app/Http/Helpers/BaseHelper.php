<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


class BaseHelper
{
    protected $errors = [];

    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
        return $this;
    }

    public function hasError()
    {
        return (bool) count($this->errors);
    }
}