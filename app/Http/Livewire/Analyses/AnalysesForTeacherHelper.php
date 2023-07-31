<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\BaseAttainment;
use tcCore\Subject;
use tcCore\User;

class AnalysesForTeacherHelper
{
    private $forUser;

    private $studentUuid;

    private $classUuid;

    public function __construct($studentUuid, $classUuid)
    {
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

    public function getRouteForDashboardShow()
    {
        return route('teacher.analyses.show', [
            'student_uuid' => $this->studentUuid,
            'class_uuid'   => $this->classUuid,
        ]);
    }

    public function getRouteForSubjectShow($subjectUuid)
    {
        return route('teacher.analyses.subject.show', [
            'student_uuid' => $this->studentUuid,
            'class_uuid'   => $this->classUuid,
            'subject'      => $subjectUuid,
        ]);
    }

    public function getRouteForAttainmentShow(BaseAttainment $baseAttainment, Subject $subject): string
    {
        return route('teacher.analyses.attainment.show', [
            'student_uuid'   => $this->studentUuid,
            'class_uuid'     => $this->classUuid,
            'baseAttainment' => $baseAttainment->uuid,
            'subject'        => $subject->uuid,
        ]);
    }

    public function getRouteForSubAttainmentShow(BaseAttainment $baseAttainment, $subject)
    {
        return route('teacher.analyses.subattainment.show', [
            'student_uuid'   => $this->studentUuid,
            'class_uuid'     => $this->classUuid,
            'baseAttainment' => $baseAttainment->uuid,
            'subject'        => $subject,
        ]);
    }

    public function getRouteForSubSubAttainmentShow($pValue, $subject)
    {
        return route('teacher.analyses.subsubattainment.show', [
            'student_uuid'   => $this->studentUuid,
            'class_uuid'     => $this->classUuid,
            'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
            'subject'        => $subject,
        ]);
    }

    public function getRouteForShowGrades()
    {
        throw new \Exception('Teacher has no route for show grades student from within analyses');
    }

    public function getLayout()
    {
        return 'layouts.app-teacher';
    }
}
