<?php

namespace tcCore\Traits;

use Illuminate\Support\Facades\Auth;

trait CanOrderGrid
{
    public $orderByDirection = 'desc';
    public $orderByColumnName = 'id';

    public function setOrderByColumnAndDirection($columnName)
    {
        if ($this->orderByColumnName === $columnName) {
            $this->orderByDirection = $this->orderByDirection == 'asc' ? 'desc' : 'asc';
            return;
        }
        $this->orderByColumnName = $columnName;
        $this->orderByDirection = 'asc';
    }
}