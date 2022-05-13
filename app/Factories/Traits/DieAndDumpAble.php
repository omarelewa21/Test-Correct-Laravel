<?php

namespace tcCore\Factories\Traits;

trait DieAndDumpAble
{
    public function dd()
    {
        dd($this);
    }

    public function dump()
    {
        dump($this);
    }
}