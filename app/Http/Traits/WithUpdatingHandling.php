<?php


namespace tcCore\Http\Traits;



use tcCore\Http\Requests\Request;

trait WithUpdatingHandling
{

    public function updating(&$name, &$value)
    {
        Request::filter($value);
    }
}