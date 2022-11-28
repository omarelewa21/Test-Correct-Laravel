<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\SchoolClass;
use tcCore\SchoolYear;
use tcCore\Student;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\User;

class FactorySchoolClass
{
    use DoWhileLoggedInTrait;
    use RandomCharactersGeneratable;

    public SchoolClass $schoolClass;
    protected $schoolClassProperties = [];

    /**
     * @param SchoolYear $schoolYear
     * @param int|null $educationLevelId if null, random educationLevel from the schoolLocation
     * @param string|null $name if null, randomized name
     * @param array $properties
     * @return static
     */
    public static function create(SchoolYear $schoolYear, int $educationLevelId = null, string $name = null, array $properties = [])
    {
        $factory = new static;

        if (!$name) {
            $name = 'school_class-' . $factory->randomCharacters(5);
        }
        if (!$educationLevelId) {
            $educationLevelId = $schoolYear->schoolLocations()->first()->educationLevels->random()->getKey();
        }

        $factory->schoolClassProperties = array_merge($factory->definition(), [
            'school_location_id' => $schoolYear->schoolLocations()->first()->getKey(),
            'school_year_id'     => $schoolYear->getKey(),
            'education_level_id' => $educationLevelId,
            'name'               => $name,
        ], $properties);

        $factory->schoolClass = SchoolClass::create($factory->schoolClassProperties);

        return $factory;
    }

    public function addStudent(User $user)
    {
        if (!$user->isA('Student')) {
            throw new \Exception('please add a user with the role student.');
        }

        Student::create([
            'user_id' => $user->getKey(),
            'class_id' => $this->schoolClass->getKey()
        ]);

        return $this;
    }

    public function addTeacher(User $user, Subject $subject)
    {
        if (!$user->isA('Teacher')) {
            throw new \Exception('please add a user with the role teacher.');
        }

        Teacher::create([
            'user_id'    => $user->getKey(),
            'subject_id' => $subject->getKey(),
            'class_id'   => $this->schoolClass->getKey()
        ]);
        return $this;
    }

    protected function definition()
    {

        $noteFillableSchoolClass = [
            'created_by',                       //for all records in example database this is NULL
            'school_location_id',               //relationship
            'education_level_id',               //relationship
            'school_year_id',                   //relationship
            'name',
            'education_level_year',                 //smaller or equal to max_years in education_levels, in relationship education_level_id
            'is_main_school_class',             //(how) do i use this?
            'do_not_overwrite_from_interface',  //?
            'demo',                             //(0)
            'visible',                          //(1)
            //do not use, these are created/used by testtakes that are used by guests
            'guest_class', //0 or leave empty?
            'test_take_id',
            //not in database, not used in SchoolClass model boot methods or anywhere else:
            'old_school_class_id',                  //??? not in database
            'subject_id',                           //not present in database!
        ];

        return [
            'school_location_id'              => null,
            'education_level_id'              => null,
            'school_year_id'                  => null,
            'name'                            => null,
            'education_level_year'            => 1,
            'is_main_school_class'            => 0,
            'do_not_overwrite_from_interface' => 1,
            'demo'                            => 0,
            'visible'                         => 1,
            'created_by'                      => null,
        ];
    }
}