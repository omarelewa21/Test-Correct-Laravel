<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class UwlrSoapEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uwlr_soap_result_id', 'key', 'object'];
}
