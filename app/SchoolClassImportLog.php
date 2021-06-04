<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class SchoolClassImportLog extends Model
{
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');

   }
}
