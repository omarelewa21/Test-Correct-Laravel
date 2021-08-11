<?php


namespace tcCore\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use tcCore\SchoolClass;
use tcCore\SchoolClassImportLog;

trait UwlrImportHandlingForController
{

    protected function setClassesVisibleAndFinalizeImport($user)
    {
        if ($user->isA('teacher')) {
            SchoolClassImportLog::where(function ($query) use ($user) {
                $query->where('checked_by_teacher_id', $user->getKey())
                    ->orWhereNotNull('checked_by_admin');
            })->update([
                'finalized' => Carbon::now()
            ]);
        }

        SchoolClass::withoutGlobalScope('visibleOnly')
            ->where('visible', 0)
            ->whereIn('id',
                SchoolClassImportLog::whereNotNull('finalized')
                    ->where(function ($query) use ($user) {
                        $query->where('checked_by_teacher_id', $user->getKey())
                            ->orWhereNotNull('checked_by_admin');
                    })
                    ->pluck('class_id')
            )
            ->update([
                'visible' => true,
            ]);
    }
}