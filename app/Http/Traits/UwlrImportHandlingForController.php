<?php


namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\SchoolClass;
use tcCore\SchoolClassImportLog;

trait UwlrImportHandlingForController
{

    protected function setClassesVisible()
    {
        SchoolClass::withoutGlobalScope('visibleOnly')->whereIn('id', SchoolClassImportLog::whereNotNull('finalized')->where('checked_by_teacher_id', Auth::id())->pluck('class_id'))->update([
            'visible' => true,
        ]);
    }
}