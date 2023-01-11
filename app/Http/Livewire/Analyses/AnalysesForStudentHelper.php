<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\BaseAttainment;
use tcCore\Subject;

class AnalysesForStudentHelper
{
    public function getForUser()
    {
        return auth()->user();
    }

    public function getRouteForDashboardShow() {
        return route('student.analyses.show');
    }

    public function getRouteForSubjectShow(Subject $subject)
    {
        return route('student.analyses.subject.show', [
            'subject' => $subject->uuid,
        ]);
    }

    public function getRouteForAttainmentShow(BaseAttainment $baseAttainment, Subject $subject): string
    {
        return route('student.analyses.attainment.show', [
            'baseAttainment' => $baseAttainment->uuid,
            'subject'        => $subject->uuid,
        ]);
    }

    public function getRouteForSubAttainmentShow(BaseAttainment $baseAttainment, $subject)
    {
        return route('student.analyses.subattainment.show', [
            'baseAttainment' => $baseAttainment->uuid,
            'subject'        => $subject,
        ]);
    }

    public function getRouteForSubSubAttainmentShow($pValue, $subject)
    {
        return route('student.analyses.subsubattainment.show', [
            'baseAttainment' => BaseAttainment::find($pValue->attainment_id)->uuid,
            'subject'        => $subject,
        ]);
    }

    public function getRouteForShowGrades()
    {
        return route('student.test-takes', ['tab' => 'graded']);
    }
    public function getLayout(){
        return 'layouts.student';
    }
}
