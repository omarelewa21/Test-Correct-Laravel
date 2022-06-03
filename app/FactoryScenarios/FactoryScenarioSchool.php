<?php

namespace tcCore\FactoryScenarios;

use Illuminate\Support\Collection;
use tcCore\Factories\FactoryTest;
use tcCore\School;

abstract class FactoryScenarioSchool
{
    public School $school;
    public Collection $schools;
    public Collection $tests;
    protected Collection $teachers;
    protected Collection $students;

    public function __construct()
    {
        $this->schools = collect([]);
    }

    public abstract static function create();

    public function getTeachers(): Collection
    {
        $this->teachers = collect([]);

        $this->schools->each(function ($school) {
            $school->schoolLocations->each(function ($schoolLocation) {
                $schoolLocation->schoolClasses->where('demo', 0)->each(function ($schoolClass) {
                    $schoolClass->teacher()->each(function ($teacher) {
                        $this->teachers->add($teacher->user);
                    });
                });
            });
        });

        return $this->teachers->unique()->values();
    }

    public function getStudents()
    {
        $this->students = collect([]);

        $this->schools->each(function ($school) {
            $school->schoolLocations->each(function ($schoolLocation) {
                $schoolLocation->schoolClasses->where('demo', 0)->each(function ($schoolClass) {
                    $schoolClass->students->each(function ($student) {
                        $this->students->add($student->user);
                    });
                });
            });
        });

        return $this->students->unique()->values();
    }

    public function seedTests()
    {
        //todo create test for each teacher record
        //  for the right period, test for russian of Piet, has to be a the period in the past.
        $this->tests = collect([]);

        $this->schools->each(function ($school) {
            $school->schoolLocations->each(function ($schoolLocation) {
                $schoolLocation->schoolClasses->where('demo', 0)->each(function ($schoolClass) {

                    $period = $schoolClass->schoolYear()->first()->periods()->first();

                    $schoolClass->teacher()->each(function ($teacher) use ($period) {

                        $this->tests->add(
                            FactoryTest::create($teacher->user)->setProperties([
                                'period_id'    => $period->getKey(),
                                'subject_id'   => $teacher->subject_id,
                                'name'         => $teacher->subject->name . ' toets',
                                'abbreviation' => $teacher->subject->abbreviation,
                            ])->addRandomQuestions()
                        );

                    });
                });
            });
        });

        return $this;
    }
}