<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Facades\Auth;
use tcCore\SchoolClass;

trait WithPlanningFeatures
{
    public $rttiExportAllowed = false;

    public function isRttiExportAllowed(): bool
    {
        return !! Auth::user()->schoolLocation->is_rtti_school_location;
    }

    public function getSchoolClassesProperty()
    {
        $filters = $this->getFiltersForSchoolClasses();
        return SchoolClass::filtered($filters)->optionList();
    }

    public function isAssignmentType()
    {
        return $this->test->isAssignment();
    }

    /**
     * @return array
     */
    private function getFiltersForSchoolClasses(): array
    {
        $filters = [
            'user_id' => auth()->id(),
            'current' => true,
        ];
        if (Auth::user()->isValidExamCoordinator()) {
            if (filled($this->test->scope)) {
                $filterAddOn = ['base_subject_id' => $this->test->subject()->value('base_subject_id')];
            } else {
                $filterAddOn = ['subject_id' => $this->test->subject_id];
            }
            $filters = $filters + $filterAddOn;
        }
        return $filters;
    }

    /**
     * Show spell checker toggle if user is allowed to use it and test contains writing questions.
     * 
     * @return bool
     */
    public function showSpellCheckerToggle(): bool
    {
        return $this->test->getAllowWscForStudentsAttribute();
    }
}