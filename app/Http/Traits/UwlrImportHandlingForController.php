<?php


namespace tcCore\Http\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SchoolClass;
use tcCore\SchoolClassImportLog;
use tcCore\User;

trait UwlrImportHandlingForController
{

    // info for ticket 2325
    // the school class import log needs a where statement where checked_by_teacher_id is not null, as well as the or wherenot null checked_by_admin
    // AND class_id in one of your classes. But not the checked_by_teacher_id being you
    // same for the school class
    // problem is that if one starts with the validation someone else can't finish it and that gives misleading messages

    protected function setClassesVisibleAndFinalizeImport(User $user)
    {
        $classIds = $user->getTeacherSchoolClassIds();
        if(!$classIds->count()){
            return true;
        }
        if ($user->isA('teacher')) {
            SchoolClassImportLog::where(function ($query){
                $query->whereNotNull('checked_by_teacher_id')
                    ->orWhereNotNull('checked_by_admin');
            })
                ->whereIn('class_id',$classIds->toArray())
                ->whereNull('finalized')
                ->update([
                'finalized' => Carbon::now()
            ]);
        }

        SchoolClass::withoutGlobalScope('visibleOnly')
            ->where('visible', 0)
            ->whereIn('id',
                SchoolClassImportLog::whereNotNull('finalized')
                    ->where(function ($query) {
                        $query->whereNotNull('checked_by_teacher_id')
                            ->orWhereNotNull('checked_by_admin');
                    })
                    ->whereIn('class_id',$classIds->toArray())
                    ->pluck('class_id')
            )
            ->update([
                'visible' => true,
            ]);
    }
}