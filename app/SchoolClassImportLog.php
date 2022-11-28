<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class SchoolClassImportLog extends Model
{
    protected $casts = [
      'finalized' => 'datetime:Y-m-d H:i:s',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id')->withTrashed();

   }
}
