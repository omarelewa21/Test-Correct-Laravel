<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\BaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\SchoolLocationCreator;
use tcCore\User;

class FactoryScenarioSchoolFormidable extends FactoryScenarioSchool
{
    public $schoolName;

    public $schoolLocationName;

    public $sectionName;

    public $schoolClassName;

    public $customer_code;

    public $teacher_one;
    public $school_location_one;

    public $baseSubjectId = BaseSubject::FRENCH;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Formidable vragencontent';

        $this->schoolLocationName = 'Formidable vragencontent';

        $this->sectionName = 'Formidable section';

        $this->schoolClassName = 'Formidable school class';

        $this->customer_code = 'FORMIDABLE';
    }

    public static function create()
    {
        $factory = new static;

        SchoolLocationCreator::createFormidableSchool($factory);
        SchoolLocationCreator::createSimpleSchoolWithOneTeacher($factory);

        return $factory;
    }


    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
                'school_location_one' => $this->school_location_one,
            ];
    }
}
