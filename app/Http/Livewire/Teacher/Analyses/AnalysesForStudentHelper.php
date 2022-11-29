<?php

namespace tcCore\Http\Livewire\Teacher\Analyses;

use tcCore\BaseAttainment;
use tcCore\Subject;
use tcCore\User;

class AnalysesForStudentHelper
{
//    private $forUser;
//
//    private $studentUuid;
//
//    private $classUuid;


    public function getForUser()
    {
        return auth()->user();
    }

    public function getRouteForAttainmentShow($pValue, Subject $subject): string
    {
        return route('student.analyses.attainment.show', [
            'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
            'subject'        => $subject->uuid,
        ]);
    }

    public function getRouteForSubjectShow($pValue){
        return route('student.analyses.subject.show', [
            'subject'      => Subject::find($pValue->subject_id)->uuid,
        ]);
    }


}
