<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestKind;

trait ToetsenbakkerTestActions
{
    public function getAllowedSubjects()
    {
        if (!$this->fileManagement) {
            return Subject::select('subjects.*')
                ->join('school_location_sections', 'school_location_sections.section_id', '=', 'subjects.section_id')
                ->where('school_location_sections.school_location_id', Auth::user()->school_location_id)
                ->get(['id', 'name'])
                ->keyBy('id');
        }

        return Subject::whereIn('section_id', $this->fileManagement->schoolLocation->schoolLocationSections()->select('section_id'))
            ->whereDemo(false)
            ->get(['id', 'name'])
            ->keyBy('id');
    }

    public function getAllowedPeriods()
    {
        $schoolLocation = $this->fileManagement?->schoolLocation ?? Auth::user()->schoolLocation;
        return Period::currentlyActive()
            ->forSchoolLocation($schoolLocation)
            ->get(['id', 'name', 'start_date', 'end_date'])
            ->keyBy('id');
    }

    public function getAllowedEducationLevels()
    {
        $schoolLocationId = $this->fileManagement?->school_location_id ?? Auth::user()->school_location_id;
        return EducationLevel::whereIn(
            'id',
            SchoolLocationEducationLevel::select('education_level_id')
                ->where('school_location_id', $schoolLocationId)
        )
            ->get(['id', 'name', 'max_years', 'uuid'])->keyBy('id');
    }
}