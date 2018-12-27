<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{

    protected $fillable = [
        'uri', 'uri_full', 'method','request','response','headers','code','ip','duration','user_id','user_agent','success',
    ];
}
