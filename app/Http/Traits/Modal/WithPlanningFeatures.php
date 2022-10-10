<?php

namespace tcCore\Http\Traits\Modal;

use tcCore\SchoolClass;

trait WithPlanningFeatures
{
    public function getSchoolClassesProperty()
    {
        return SchoolClass::filtered(
            ['user_id' => auth()->id(), 'current' => true,],
            ['school_location_id' => 'asc']
        )
            ->get(['id', 'name', 'school_location_id'])
            ->map(fn ($class) => ['value' => $class->id, 'label' => $class->name]);
    }

    public function isAssessmentType()
    {
        return $this->test->isAssignment();
    }
}