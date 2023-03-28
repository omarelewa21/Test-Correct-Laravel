<?php namespace tcCore\Lib\Repositories;


use tcCore\AverageRating;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\User;

class SubjectRepository
{
    // Get subjects of student with base subject
    public static function getSubjectsOfStudent(User $student)
    {
        $subjectIdsBuilder = AverageRating::where('user_id', $student->getKey())->distinct()->select('subject_id');
        return Subject::whereIn('id', $subjectIdsBuilder)->with('baseSubject')->get();
    }

    // Get subjects of school location(s)
    public static function getSubjectsOfSchoolLocation(SchoolLocation $schoolLocation)
    {
        return Subject::join('sections', 'sections.id', '=', 'subjects.section_id')
            ->join('school_location_sections', 'sections.id', '=', 'school_location_sections.section_id')
            ->join('school_locations', 'school_locations.id', '=', 'school_location_sections.school_location_id')
            ->where('school_locations.id', $schoolLocation->getKey())
            ->with('baseSubject')
            ->get(['subjects.*']);
    }

    // Get subjects of school
    public static function getSubjectsOfSchool(School $school)
    {
        return Subject::join('sections', 'sections.id', '=', 'subjects.section_id')
            ->join('school_location_sections', 'school_location_sections.section_id', '=', 'sections.id')
            ->join('school_locations', 'school_locations.id', '=', 'school_location_sections.school_location_id')
            ->join('schools', 'schools.id', '=', 'school_locations.school_id')
            ->where('schools.id', $school->getKey())
            ->with('baseSubject')
            ->get(['subjects.*']);
    }
}