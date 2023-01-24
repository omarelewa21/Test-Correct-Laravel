<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use tcCore\EducationLevel;
use tcCore\Period;
use tcCore\SchoolLocationEducationLevel;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestKind;

trait ToetsenbakkerTestActions
{
    public function getAllowedSubjects()
    {
        if (filled($this->fileManagement->subject_id)) {
            return Subject::where('id', $this->fileManagement->subject_id)->get(['id', 'name'])->keyBy('id');
        }
        return Subject::whereIn(
            'id',
            Teacher::select('subject_id')
                ->where('user_id', $this->fileManagement->user_id)
        )
            ->whereDemo(false)
            ->get(['id', 'name'])
            ->keyBy('id');
    }

    public function getAllowedPeriods()
    {
        return Period::currentlyActive()
            ->forSchoolLocation($this->fileManagement->schoolLocation)
            ->get(['id', 'name', 'start_date', 'end_date'])
            ->keyBy('id');
    }

    public function getAllowedEducationLevels()
    {
        return EducationLevel::whereIn(
            'id',
            SchoolLocationEducationLevel::select('education_level_id')
                ->where('school_location_id', $this->fileManagement->school_location_id)
        )
            ->get(['id', 'name', 'max_years', 'uuid'])->keyBy('id');
    }
}