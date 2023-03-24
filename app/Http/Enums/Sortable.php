<?php

namespace tcCore\Http\Enums;

interface Sortable
{
    public function getSortWeight(): int;
}