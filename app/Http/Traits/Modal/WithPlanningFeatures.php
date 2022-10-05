<?php

namespace tcCore\Http\Traits\Modal;

use tcCore\SchoolClass;

trait WithPlanningFeatures
{
    public function getSchoolClassesProperty()
    {
        return SchoolClass::filtered(
            ['user_id' => auth()->id(), 'current' => true,]
        )->optionList();
    }

    public function isAssessmentType()
    {
        return $this->test->isAssignment();
    }
}