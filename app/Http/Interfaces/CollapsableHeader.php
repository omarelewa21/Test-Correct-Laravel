<?php

namespace tcCore\Http\Interfaces;

interface CollapsableHeader
{
    public function handleHeaderCollapse($args);
    public function redirectBack();
}