<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class RttiExportLog extends Model
{
    protected $guarded = [];

    protected $casts = [
      'has_errors' => 'boolean',
    ];
}
