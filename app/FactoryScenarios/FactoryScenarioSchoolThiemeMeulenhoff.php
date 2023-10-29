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
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\SchoolLocationCreator;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class FactoryScenarioSchoolThiemeMeulenhoff extends FactoryScenarioSchool
{
    public $schoolName;
    public $schoolLocationName;
    public $schoolYearYear;

    public $sectionName;

    public $schoolClassName;

    public $customer_code;

    public $teacher_one;
    public $school_location_one;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Thieme Meulenhoff vragencontent';

        $this->schoolLocationName = 'Thieme Meulenhoff vragencontent';

        $this->sectionName = 'Thieme Meulenhoff section';

        $this->schoolClassName = 'Thieme Meulenhoff school class';

        $this->customer_code = 'THIEMEMEULENHOFF';
    }

    public static function create()
    {
        $factory = new static;

        SchoolLocationCreator::createThiemeMeulenHoffSchool($factory);
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
