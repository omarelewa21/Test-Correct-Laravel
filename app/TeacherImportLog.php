<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class TeacherImportLog extends Model
{
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');

    }
}

