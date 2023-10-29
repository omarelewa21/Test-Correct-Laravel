<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

abstract class FactoryScenarioSchool
{
    public School $school;
    public Collection $schools;
    public Collection $tests;
    protected Collection $teachers;
    protected Collection $students;


    public $baseSubjectId = BaseSubject::DUTCH;


    public function __construct()
    {
        $this->schools = collect([]);
        $this->teachers = collect([]);
        $this->students = collect([]);
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

    public function getData()
    {
        return [
            'students'         => $this->getStudents(),
            'student1'         => $this->students->first(),
            'teachers'         => $this->getTeachers(),
            'user'             => $this->teachers->first(),
            'teacher1'         => $this->teachers->first(),
            'school'           => $this->school,
            'school_locations' => $this->school->schoolLocations,
        ];
    }

    public function getMinimalData()
    {
        return collect($this->getData())->map(function ($item, $key) {
            if ($item instanceof Collection) {
                //transform each
                $array = [];
                $item->each(function ($subItem) use (&$array) {
                    $array[] = $this->transformModelToArray($subItem);
                });
                return $array;
            }
            return $this->transformModelToArray($item);
        });
    }

    public function toJson()
    {
        return $this->getMinimalData()->toJson();
    }

    public function toArray()
    {
        return $this->getMinimalData()->toArray();
    }

    protected function transformModelToArray($model)
    {
        if ($model instanceof User) {
            return [
                'id'       => $model->getKey(),
                'uuid'     => $model->uuid,
                'username' => $model->username,
                'role'     => $model->roles->first()->name,
            ];
        }
        if ($model instanceof SchoolLocation || $model instanceof School) {
            return [
                'id'            => $model->getKey(),
                'uuid'          => $model->uuid,
                'name'          => $model->name,
                'customer_code' => $model->customer_code,
            ];
        }
    }
}
