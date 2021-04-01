<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class TestTakeStatusLog extends Model
{
    
    protected $fillable = ['test_take_id', 'test_take_status'];

}
