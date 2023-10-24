<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryTestParticipant;
use tcCore\Factories\FactoryTestTake;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Scopes\ArchivedScope;
use tcCore\Teacher;
use tcCore\Test;

class FactoryScenarioSchoolPValue extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;
    protected $subjectName;
    public $baseSubjectId;

    protected $schoolClassName;

    /**
     * Create a complete school scenario with the bare necessities
     * 1 school, 1 school location
     * - without shared sections, one section only
     * - one school year, one period
     * - 1 teacher, 3 students
     *
     * Subject: name 'Nederlandse Gramatica', baseSubjectId '1', section 'Nederlands',
     * One school year, with period from '1 jan / 31 dec'
     */
    public static function create()
    {
        $factory = new static;
        //every subsequent scenario get a new name SimpleSchoolGroup001, SimpleSchoolGroup002, etc.


        //create school
        $school = FactorySchool::create('PValueSchool')->school;
        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, 'PValueSchoolLocation')->addEducationlevels([
            1, // alleen vwo
        ])->schoolLocation;

        $schoolLocation->allow_analyses = true;
        //create school year and full year period for the current year
        // vernietig alle schooljaren zodat de demo schooljaar niet interfereerd;
        $schoolLocation->schoolYears()->delete();

        $schoolYearLocations = collect(range(0, 5))->map(function ($year) use ($schoolLocation) {
            $year = (int)Carbon::today()
                ->subYear($year)
                ->format('Y');
            return FactorySchoolYear::create($schoolLocation, $year)
                ->addPeriodFullYear('Period ' . $year)->schoolYear;
        });


        //create section and subject
        $desiredSections = [
            ['base_subject_id' => 1, 'name' => 'Nederlands'],
            ['base_subject_id' => 24, 'name' => 'Duits'],
            ['base_subject_id' => 23, 'name' => 'Frans'],
//            ['base_subject_id' => 11, 'name' => 'Biologie'],
//            ['base_subject_id' => 9, 'name' => 'Natuurkunde'],
//            ['base_subject_id' => 5, 'name' => 'Wiskunde A'],
//            ['base_subject_id' => 22, 'name' => 'Engels'],
        ];
        $teachers = [];
        $sections = [];
// maak de secties aan;
        foreach ($desiredSections as $sectionStruct) {
            $baseSubject = BaseSubject::find($sectionStruct['base_subject_id']);
            $section = FactorySection::create($schoolLocation, $baseSubject->name)
                ->addSubject($baseSubject, $baseSubject->name)->section;
            $teachers[$sectionStruct['name']] = FactoryUser::createTeacher($schoolLocation, false, ['name' => 'teacher_' . $sectionStruct['name']])->user;
            $sections[$sectionStruct['name']] = $section;
        }

        $students = collect([
            'student_p_value_1@sobit.nl',
            'student_p_value_2@sobit.nl',
            'student_p_value_3@sobit.nl',
        ])->map(function ($username) use ($schoolLocation) {
            return FactoryUser::createStudent($schoolLocation, ['username' => $username])->user;
        });


        //create school class with teacher and students records, add the teacher-user, create student-users
        foreach ($schoolYearLocations as $key => $schoolYearLocation) {
            $schoolClass = FactorySchoolClass::create(
                $schoolYearLocation,
                1,
                sprintf('klas  %s', $schoolYearLocation->year),
                ['education_level_year' => 6 - $key]
            );
            foreach ($desiredSections as $desiredSection) {
                $schoolClass->addTeacher($teachers[$desiredSection['name']], $sections[$desiredSection['name']]->subjects()->first());

            }
            foreach ($students as $student) {
                $schoolClass->addStudent($student);
            }
        }

        Teacher::whereIn('user_id', collect($teachers)->map(fn($user) => $user->id))
            ->with(['subject', 'schoolClass', 'schoolClass.schoolYear', 'schoolClass.schoolYear.periods', 'user'])
            ->get()
            ->each(function ($teacher) {
                $testName = sprintf('%s %s', $teacher->schoolClass->schoolYear->year, $teacher->subject->name);
                auth()->login($teacher->user);
                $test = FactoryTest::create($teacher->user)
                    ->setProperties([
                        'name'       => $testName,
                        'subject_id' => $teacher->subject_id
                    ])
                    ->addQuestions([
                        FactoryQuestionOpenShort::create(),
                        FactoryQuestionMultipleChoiceTrueFalse::create(),
                    ])->getTestModel();

                ArchivedScope::$skipScope = true;

                FactoryTestTake::create($test, $teacher->user)
                    ->setProperties(['period_id' => $teacher->schoolClass->schoolYear->periods->first()->id])
                    ->addParticipants([
                        FactoryTestParticipant::makeForAllUsersInClass($teacher->class_id)
                    ])
                    ->setStatusTakingTest()
                    ->setTestParticipantsTakingTest()
                    ->fillTestParticipantsAnswers()
                    ->setStatusTaken()
                    ->setStatusDiscussing()
                    ->addTeacherAnswerRatings()
                    ->setNormalizedScores()
                    ->setStatusRated();
            });


        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }

    protected function generateUniqueSchoolName()
    {
        for ($i = 1; $i < 20; $i++) {
            $uniqueSchoolName = $this->schoolName . sprintf("%03d", $i);
            $uniqueSchoolLocationName = $this->schoolLocationName . sprintf("%03d", $i);
            if (!School::where('name', $uniqueSchoolName)->count() && !SchoolLocation::where('name',
                    $uniqueSchoolLocationName)) {
                $this->schoolName = $uniqueSchoolName;
                $this->schoolLocationName = $uniqueSchoolLocationName;
                break;
            }
        }

    }
}
