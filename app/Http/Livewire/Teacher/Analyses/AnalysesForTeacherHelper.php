<?php

namespace tcCore\Http\Livewire\Teacher\Analyses;

use tcCore\BaseAttainment;
use tcCore\Subject;
use tcCore\User;

class AnalysesForTeacherHelper
{
    private $forUser;

    private $studentUuid;

    private $classUuid;

    public function __construct($studentUuid, $classUuid) {
        $this->studentUuid = $studentUuid;
        $this->classUuid = $classUuid;
    }

    public function getForUser()
    {
        if (!$this->forUser) {
            $this->forUser = User::whereUuid($this->studentUuid)->first();
        }

        return $this->forUser;
    }

    public function getRouteForAttainmentShow($pValue, Subject $subject): string
    {
        return route('teacher.analyses.attainment.show', [
            'student_uuid'   => $this->studentUuid,
            'class_uuid'     => $this->classUuid,
            'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
            'subject'        => $subject->uuid,
        ]);
    }

    public function getRouteForSubjectShow($pValue){
        return route('teacher.analyses.subject.show', [
            'student_uuid' => request('student_uuid'),
            'class_uuid'   => request('class_uuid'),
            'subject'      => Subject::find($pValue->subject_id)->uuid,
        ]);
    }


}
